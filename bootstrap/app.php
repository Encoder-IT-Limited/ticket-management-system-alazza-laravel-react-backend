<?php

use App\Http\Middleware\Cors;
use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\IsAdmin;
use App\Jobs\BuildAndStoreReportJob;
use App\Jobs\SendReportEmailJob;
use App\Models\Services\TicketService;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\UnauthorizedException;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Log;

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
    // schedule jobs
    ->withSchedule(function (Schedule $schedule) {
        // Weekly: run Monday 01:00
        $schedule->call(function () {
            try {
                $start = now()->startOfWeek()->subWeek()->toDateString();
                $end = now()->startOfWeek()->subDay()->toDateString();

                $fileInfo = (new TicketService())->processReport($start, $end, true);

                $subject = "Weekly Report: {$start} to {$end}";
                $message = "Please find attached the weekly report for the period from {$start} to {$end}.";

                User::whereHas('permissions', function ($q) {
                    $q->where('slug', 'weekly-reports');
                })
                    ->select('id', 'name', 'email')
                    ->chunk(100, function ($users) use ($fileInfo, $subject, $message) {
                        foreach ($users as $user) {
                            SendReportEmailJob::dispatch($user, $subject, $fileInfo, $message);
                        }
                    });
            } catch (\Exception $e) {
                Log::error('Weekly report schedule failed: ' . $e->getMessage());
            }
        })->weeklyOn(1, '01:00');

        // Monthly: day 1, 01:30
        $schedule->call(function () {
            try {
                $start = now()->startOfMonth()->subMonth()->toDateString();
                $end = now()->subMonth()->endOfMonth()->toDateString();
                $fileInfo = (new TicketService())->processReport($start, $end, false);
                $subject = "Monthly Report: {$start} to {$end}";
                $message = "Please find attached the monthly report for the period from {$start} to {$end}.";

                User::whereHas('permissions', function ($q) {
                    $q->where('slug', 'monthly-reports');
                })->select('id', 'name', 'email')
                    ->chunk(100, function ($users) use ($fileInfo, $subject, $message) {
                        foreach ($users as $user) {
                            SendReportEmailJob::dispatch($user, $subject, $fileInfo, $message);
                        }
                    });
            } catch (\Exception $e) {
                Log::error('Monthly report schedule failed: ' . $e->getMessage());
            }
        })->monthlyOn(1, '01:30');
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Resource not found', 404);
            }
            return response()->view('errors.error', ['error' => $e,], 404);
        });
        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Resource not found', 404);
            }
            return response()->view('errors.error', ['error' => $e,], 404);
        });
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Unauthenticated', 401);
            }
            return response()->view('errors.error', ['error' => $e,], 401);
        });
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            if ($request->is('api/*')) {
                return failureResponse('Unauthorized action. You need permission to view.', 403);
            }
            return response()->view('errors.error', ['error' => $e,], 403);
        });
        //        $exceptions->render(function (Throwable $e, Request $request) {
        //            if ($request->is('api/*')) {
        //                return failureResponse($e->getMessage(), $e->getCode());
        //            }
        //            return response()->view('errors.error', ['error' => $e,], $e->getCode());
        //        });
    })->create();
