<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'app.customization' => \App\Http\Middleware\AppCustomizationMiddleware::class,
            'inertia.panel' => \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
        $middleware->api('app.customization');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Treat all /api/* requests as JSON, even without an Accept header.
        $wantsJson = fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($wantsJson) {
            if ($request->is('panel*') && ! $request->expectsJson()) {
                return redirect()->guest(route('panel.login'));
            }

            if ($wantsJson($request)) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return null;
        });

        // Global JSON error envelope for the API.
        $exceptions->render(function (\Throwable $e, Request $request) use ($wantsJson): ?JsonResponse {
            if (! $wantsJson($request)) {
                return null;
            }

            return match (true) {
                $e instanceof AuthenticationException => response()->json([
                    'message' => 'Unauthenticated.',
                ], 401),

                $e instanceof ValidationException => response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], $e->status),

                $e instanceof AuthorizationException => response()->json([
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                ], 403),

                $e instanceof ModelNotFoundException => response()->json([
                    'message' => 'Resource not found.',
                ], 404),

                $e instanceof NotFoundHttpException => response()->json([
                    'message' => 'The requested endpoint was not found.',
                ], 404),

                $e instanceof MethodNotAllowedHttpException => response()->json([
                    'message' => 'The HTTP method is not allowed for this endpoint.',
                ], 405),

                $e instanceof HttpExceptionInterface => response()->json([
                    'message' => $e->getMessage() ?: 'HTTP error.',
                ], $e->getStatusCode()),

                default => response()->json([
                    'message' => config('app.debug') ? $e->getMessage() : 'Server error.',
                    ...(config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->map(fn ($t) => Arr::except($t, ['args']))->all(),
                    ] : []),
                ], 500),
            };
        });
    })->create();
