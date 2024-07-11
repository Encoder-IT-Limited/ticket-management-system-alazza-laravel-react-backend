<?php

namespace App\Providers;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (User $user, string $token) {
//            return 'https://example.com/reset-password?token='.$token;
            return config('app.frontend_url').'/reset-password?token='.$token;
        });
    }
}
