<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Cors;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\IsAdmin;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'json' => ForceJsonResponse::class,
            'cors' => Cors::class,

            'abilities' => CheckAbilities::class,
            'ability' => CheckForAnyAbility::class,

            'is_admin' => IsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Resource not found', 404);
            }
            return response()->view('errors.error', ['error' => $e,], 500);
        });
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Resource not found', 404);
            }
            return response()->view('errors.error', ['error' => $e,], 500);
        });
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Unauthenticated', 401);
            }
            return response()->view('errors.error', ['error' => $e,], 500);
        });
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Unauthorized action. You need permission to view.', 403);
            }
            return response()->view('errors.error', ['error' => $e,], 500);
        });
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse($e->getMessage(), 500);
            }
            return response()->view('errors.error', ['error' => $e,], 500);
        });
    })->create();
