<?php

use App\Enums\ErrorCode;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        $middleware->statefulApi();

        // Rate limiting per endpoint group
        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Always render JSON for API routes
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // 401 Unauthenticated
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code'    => ErrorCode::UNAUTHORIZED,
                        'message' => 'Unauthenticated. Please provide a valid token.',
                        'details' => [],
                    ],
                ], 401);
            }
        });

        // 422 Validation
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $details = collect($e->errors())
                    ->flatMap(fn ($messages, $field) => collect($messages)->map(fn ($msg) => [
                        'field' => $field,
                        'issue' => $msg,
                    ]))
                    ->values()
                    ->toArray();

                return response()->json([
                    'error' => [
                        'code'    => ErrorCode::VALIDATION_ERROR,
                        'message' => 'Data tidak valid.',
                        'details' => $details,
                    ],
                ], 422);
            }
        });

        // 404 Not Found
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'error' => [
                        'code'    => ErrorCode::NOT_FOUND,
                        'message' => 'Resource tidak ditemukan.',
                        'details' => [],
                    ],
                ], 404);
            }
        });

        // Generic HTTP exceptions (403, 429, etc.)
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->is('api/*') || $request->expectsJson()) {
                $status = $e->getStatusCode();
                $code = match ($status) {
                    403 => ErrorCode::FORBIDDEN,
                    429 => ErrorCode::RATE_LIMITED,
                    default => ErrorCode::SERVER_ERROR,
                };

                return response()->json([
                    'error' => [
                        'code'    => $code,
                        'message' => $e->getMessage() ?: 'Terjadi kesalahan.',
                        'details' => [],
                    ],
                ], $status);
            }
        });
    })->create();
