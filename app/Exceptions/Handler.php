<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Validation\ValidationException; // مهم: این خط را اضافه کنید
use Illuminate\Auth\AuthenticationException; // مهم: این خط را اضافه کنید

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($request, Throwable $exception)
    {
        // *** این بخش برای هندل کردن ValidationException (خطاهای فرم) ***
        if ($request->expectsJson() && $exception instanceof ValidationException) {
            return response()->json([
                'message' => 'اطلاعات ارسالی نامعتبر است.',
                'errors' => $exception->errors(),
            ], 422); // 422 Unprocessable Entity
        }

        // *** این بخش برای هندل کردن AuthenticationException (خطای احراز هویت) ***
        // این مورد زمانی رخ می دهد که کاربر لاگین نیست و سعی در دسترسی به روت محافظت شده دارد
        if ($request->expectsJson() && $exception instanceof AuthenticationException) {
            return response()->json(['message' => 'Unauthenticated.'], 401); // 401 Unauthorized
        }


        // برای سایر استثنائات، رفتار پیش‌فرض را انجام می‌دهد.
        return parent::render($request, $exception);
    }
}
