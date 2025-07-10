<?php

namespace App\Providers;

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
    ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
        // آدرس جدید و اصلاح شده که مستقیم به فایل اشاره می‌کند
        return config('app.frontend_url')."/password-reset.html?token=$token&email={$notifiable->getEmailForPasswordReset()}";
    });
}
}
