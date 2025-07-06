<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class AuthenticatedSessionController extends Controller
{
/**
     * Handle an incoming authentication request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): JsonResponse
    {
        // ۱. ورودی را از ریکوست دریافت می‌کنیم (فرانت‌اند آن را با کلید 'email' ارسال می‌کند)
        $identifier = $request->input('email');

        // ۲. تشخیص می‌دهیم که ورودی ایمیل است یا شماره تلفن
        $fieldType = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        // ۳. اطلاعات کاربری را برای تلاش ورود آماده می‌کنیم
        $credentials = [
            $fieldType => $identifier,
            'password' => $request->password
        ];

        // ۴. تلاش برای احراز هویت
        if (!Auth::attempt($credentials)) {
            // اگر احراز هویت ناموفق بود
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => __('auth.failed'), // پیام خطا برای فرانت‌اند همچنان روی فیلد email ارسال می‌شود
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
