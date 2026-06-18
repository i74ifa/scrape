<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ImageClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageClassifierController extends Controller
{
    public function __construct(private readonly ImageClassifier $classifier)
    {
    }

    /**
     * Classify an uploaded image with the Teachable Machine model.
     *
     * POST /api/classify
     *   image: uploaded file (jpg/png/webp)
     *   top:   optional int — limit to the top N predictions
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'image' => ['required', 'image', 'max:5120'], // 5 MB
            'top' => ['nullable', 'integer', 'min:1'],
        ]);

        // The daemon reads files from disk by absolute path, so persist the
        // upload to a temp file and hand over its real path.
        $path = $request->file('image')->getRealPath();
        $top = $request->filled('top') ? (int) $request->input('top') : null;

        try {
            $results = $this->classifier->classify([$path], $top);
        } catch (\RuntimeException $e) {
            return response()->json([
                'message' => 'Classification failed.',
                'error' => $e->getMessage(),
            ], 500);
        }

        // classify() returns one entry per file; we sent exactly one.
        $result = $results[0] ?? null;

        if ($result === null || ! empty($result['error'])) {
            return response()->json([
                'message' => 'Classification failed.',
                'error' => $result['error'] ?? 'No result returned.',
            ], 422);
        }

        return response()->json([
            'predictions' => $result['predictions'],
        ]);
    }
}
