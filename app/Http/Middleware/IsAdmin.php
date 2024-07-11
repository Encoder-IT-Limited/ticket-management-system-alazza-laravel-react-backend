<?php

namespace App\Http\Middleware;

use App\Enums\GuardEnums as Guard;
use App\Traits\ApiResponseTrait;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    use ApiResponseTrait;

    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Guard::Admin->value;
        // if (Auth::guard('admin')->check()) {
        // return redirect()->intended($next($request));
        // }
        // return redirect()->route('admin.auth.login');

        if (!auth($guard)->check() || !auth($guard)->user()->tokenCan('check-admin')) {
            return $this->failure('Unauthorized.', 401);
        }
        $checkTokenDate = Carbon::now();
        $tokenEndData = Auth::guard($guard)?->user()?->token()?->expires_at;
        $tokenEndData = Carbon::parse($tokenEndData);

        $dateResult = $tokenEndData->gte($checkTokenDate);
        if (!$dateResult) {
            return $this->failure('Token Expired.', 401);
        }

        return $next($request);
    }
}
