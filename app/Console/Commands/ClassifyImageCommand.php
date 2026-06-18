<?php

namespace App\Console\Commands;

use App\Services\ImageClassifier;
use Illuminate\Console\Command;

class ClassifyImageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'model:classify {image : Path to the image file to classify} {--top= : Limit to the top N predictions} {--no-daemon : Skip the daemon and spawn the one-shot CLI directly}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Classify an image with the Teachable Machine model, preferring the persistent daemon';

    /**
     * Execute the console command.
     */
    public function handle(ImageClassifier $classifier): int
    {
        $image = $this->resolveImagePath($this->argument('image'));
        $top = $this->option('top') !== null ? (int) $this->option('top') : null;

        try {
            // Prefer the daemon; fall back to the one-shot CLI when it's down,
            // warning so the slower path isn't silent.
            if (! $this->option('no-daemon')) {
                $results = $classifier->classifyViaDaemon([$image], $top);

                if ($results === null) {
                    $this->warn('Classifier daemon unavailable — falling back to one-shot CLI. Start it with: cd model && npm run serve');
                    $results = $classifier->classifyViaCli([$image], $top);
                }
            } else {
                $results = $classifier->classifyViaCli([$image], $top);
            }
        } catch (\RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        return $this->render($results);
    }

    /**
     * Pretty-print results; FAILURE if any image errored.
     */
    private function render(array $results): int
    {
        $this->line(json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $hasError = collect($results)->contains(fn ($r) => ! empty($r['error']));

        return $hasError ? self::FAILURE : self::SUCCESS;
    }

    /**
     * The daemon reads files from its own working directory, so hand it an
     * absolute path. A bare/relative path is resolved against the app root.
     */
    private function resolveImagePath(string $image): string
    {
        if (str_starts_with($image, '/') || preg_match('/^[A-Za-z]:[\\\\\/]/', $image)) {
            return $image;
        }

        return base_path($image);
    }
}
