<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Process\Process;

/**
 * Classifies images with the Teachable Machine model.
 *
 * Prefers the persistent Node daemon (model/server.js), which keeps tfjs-node +
 * the model loaded so each request is ~16ms instead of the ~700ms cold start the
 * one-shot CLI pays. Falls back to spawning `node cli.js` when the daemon is down.
 *
 * Shared by the model:classify command and the API endpoint so both paths behave
 * identically.
 */
class ImageClassifier
{
    /**
     * Classify one or more absolute image paths.
     *
     * @param  string[]  $files     Absolute paths to image files.
     * @param  int|null  $top       Limit each result to the top N predictions.
     * @param  bool      $useDaemon Try the daemon first (default). When false,
     *                              goes straight to the one-shot CLI.
     * @return array<int, array{file: string, predictions: array, error: string|null}>
     */
    public function classify(array $files, ?int $top = null, bool $useDaemon = true): array
    {
        if ($useDaemon) {
            $result = $this->classifyViaDaemon($files, $top);

            if ($result !== null) {
                return $result;
            }
        }

        return $this->classifyViaCli($files, $top);
    }

    /**
     * Send the images to the running daemon. Returns the decoded result array,
     * or null if the daemon could not be reached (caller should fall back).
     */
    public function classifyViaDaemon(array $files, ?int $top = null): ?array
    {
        $url = rtrim(config('services.classifier.url'), '/');

        $payload = ['files' => array_values($files)];
        if ($top !== null) {
            $payload['top'] = $top;
        }

        try {
            $response = Http::connectTimeout((int) config('services.classifier.connect_timeout', 1))
                ->timeout((int) config('services.classifier.timeout', 120))
                ->acceptJson()
                ->post("{$url}/classify", $payload);
        } catch (\Throwable) {
            // Connection refused / DNS / timeout: daemon is not up.
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    /**
     * Classify a remote image by URL. The daemon/CLI read local files, so the
     * URL is downloaded to a temp file first and cleaned up afterwards.
     *
     * Returns the single result entry ({file, predictions, error}), or null if
     * the image could not be downloaded — callers should treat null as "unknown"
     * rather than fatal.
     */
    public function classifyUrl(string $url, ?int $top = null): ?array
    {
        $tmp = $this->downloadToTemp($url);

        if ($tmp === null) {
            return null;
        }

        try {
            $results = $this->classify([$tmp], $top);

            return $results[0] ?? null;
        } finally {
            @unlink($tmp);
        }
    }

    /**
     * Convenience: the single best-matching label for a remote image, or null
     * when the image can't be downloaded/classified. Never throws.
     */
    public function topLabel(string $url): ?string
    {
        try {
            $result = $this->classifyUrl($url, 1);
        } catch (\Throwable) {
            return null;
        }

        return $result['predictions'][0]['className'] ?? null;
    }

    /**
     * Map a predicted category label to its estimated shipping weight in grams,
     * using the services.classifier.weights table. Returns null for an unknown
     * or null label so callers can fall back to their own default.
     */
    public function weightForLabel(?string $label): ?int
    {
        if ($label === null) {
            return null;
        }

        $grams = config("services.classifier.weights.{$label}");

        return $grams !== null ? (int) $grams : null;
    }

    /**
     * Download a URL to a temp file. Returns the absolute path, or null on
     * failure. tfjs sniffs the image format from the bytes, so no extension
     * is needed.
     */
    private function downloadToTemp(string $url): ?string
    {
        try {
            $response = Http::timeout((int) config('services.classifier.timeout', 120))->get($url);
        } catch (\Throwable) {
            return null;
        }

        if (! $response->successful()) {
            return null;
        }

        $path = tempnam(sys_get_temp_dir(), 'classify_');

        if ($path === false || file_put_contents($path, $response->body()) === false) {
            return null;
        }

        return $path;
    }

    /**
     * Fallback path: spawn `node cli.js` for a one-shot classification.
     *
     * @throws \RuntimeException When the CLI output cannot be parsed as JSON.
     */
    public function classifyViaCli(array $files, ?int $top = null): array
    {
        $command = ['node', base_path('model/cli.js'), ...array_values($files), '--json'];

        if ($top !== null) {
            $command[] = '--top';
            $command[] = (string) $top;
        }

        $process = new Process($command, base_path());
        $process->setTimeout((int) config('services.classifier.timeout', 120));
        $process->run();

        $output = trim($process->getOutput());
        $json = json_decode($output, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException(
                'Failed to parse JSON from model/cli.js: '.($process->getErrorOutput() ?: $output)
            );
        }

        return $json;
    }
}
