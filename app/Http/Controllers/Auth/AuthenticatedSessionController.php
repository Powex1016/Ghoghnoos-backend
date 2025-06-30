<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse; // مهم: این خط باید باشد

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): JsonResponse // نوع بازگشتی JsonResponse
    {
        // تلاش برای احراز هویت کاربر
        if (!Auth::attempt($request->only('email', 'password'))) {
            // اگر احراز هویت ناموفق بود
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.failed'), // پیام خطای پیش فرض لاراول برای ورود ناموفق
            ]);
        }

        // اگر احراز هویت موفق بود
        $user = Auth::user(); // دریافت کاربر احراز هویت شده

        // ساخت توکن Sanctum برای کاربر
        $token = $user->createToken('auth_token')->plainTextToken;

        // بازگرداندن پاسخ JSON شامل کاربر، توکن و پیام موفقیت
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'ورود با موفقیت انجام شد.'
        ], 200); // 200 OK
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        // برای خروج در API، توکن‌های Sanctum کاربر فعلی را حذف می‌کنیم.
        if (Auth::user()) {
            Auth::user()->tokens()->delete(); // حذف تمام توکن‌های Sanctum برای کاربر فعلی
        }

        // بازگرداندن پاسخ 204 No Content برای نشان دادن موفقیت بدون محتوا
        return response()->noContent();
    }
}
