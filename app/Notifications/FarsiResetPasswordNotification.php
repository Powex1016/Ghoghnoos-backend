<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

class FarsiResetPasswordNotification extends ResetPasswordNotification
{
    use Queueable;

    public function toMail($notifiable)
    {
        // ساختن لینک بازنشانی
        $resetUrl = url(config('app.frontend_url')."/password-reset.html?token={$this->token}&email={$notifiable->getEmailForPasswordReset()}");

        // دریافت زمان انقضای توکن
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        // کد HTML برای دکمه آبی رنگ
        $buttonHtml = '<a href="'. $resetUrl .'" class="button button-primary" target="_blank" rel="noopener" style="box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Helvetica, Arial, sans-serif, \'Apple Color Emoji\', \'Segoe UI Emoji\', \'Segoe UI Symbol\'; position: relative; -webkit-text-size-adjust: none; border-radius: 4px; color: #fff; display: inline-block; overflow: hidden; text-decoration: none; background-color: #3860f6; border-bottom: 8px solid #3860f6; border-left: 18px solid #3860f6; border-right: 18px solid #3860f6; border-top: 8px solid #3860f6;">بازنشانی رمز عبور</a>';

        // متن راهنمای فارسی به همراه لینک
        $subcopyHtml = '<p style="font-size: 12px; line-height: 1.5em; margin-top: 25px; text-align: center;">اگر برای کلیک کردن روی دکمه «بازنشانی رمز عبور» مشکل دارید، آدرس زیر را کپی کرده و در مرورگر وب خود پیست کنید:<br><span class="break-all" style="word-break: break-all;"><a href="'. $resetUrl .'" style="color: #a8aaaf;">'. $resetUrl .'</a></span></p>';

        return (new MailMessage)
            ->subject("اطلاعیه بازنشانی رمز عبور")
            ->greeting("سلام!")
            ->line("شما این ایمیل را به این دلیل دریافت کرده‌اید که ما یک درخواست بازنشانی رمز عبور برای حساب شما دریافت کردیم.")
            ->line(new HtmlString('<div style="text-align: center; margin: 30px 0;">'.$buttonHtml.'</div>')) // اضافه کردن دکمه
            ->line("این لینک بازنشانی رمز عبور تا {$expire} دقیقه دیگر منقضی می‌شود.")
            ->line("اگر شما درخواست بازنشانی رمز عبور نداده‌اید، نیاز به هیچ اقدام دیگری نیست.")
            ->line(new HtmlString($subcopyHtml)) // اضافه کردن متن راهنمای فارسی
            ->line("با احترام،")
            ->salutation("تیم ماساژ ققنوس");
    }
}
