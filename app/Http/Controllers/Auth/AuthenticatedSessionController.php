<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest; // اضافه شده: برای اعتبارسنجی درخواست ورود
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse; // اضافه شده: برای نوع بازگشتی JSON

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(LoginRequest $request): JsonResponse // تغییر نوع بازگشتی و استفاده از LoginRequest
    {
        $request->authenticate(); // این متد اعتبار سنجی و لاگین کاربر را انجام می‌دهد.

        // خطوط مربوط به مدیریت Session را حذف یا کامنت کنید، زیرا در API نیازی به آن نداریم.
        // $request->session()->regenerate();

        $user = $request->user(); // دریافت کاربر احراز هویت شده

        // بازگرداندن پاسخ JSON شامل اطلاعات کاربر و توکن Sanctum
        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken, // ایجاد توکن Sanctum
            'message' => 'ورود با موفقیت انجام شد.'
        ]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response // نوع بازگشتی Response است زیرا 204 No Content برمی‌گرداند
    {
        // برای خروج در API، توکن‌های Sanctum کاربر فعلی را حذف می‌کنیم.
        if (Auth::user()) {
            Auth::user()->tokens()->delete(); // حذف تمام توکن‌های Sanctum برای کاربر فعلی
        }

        // خطوط مربوط به مدیریت Session سنتی (web guard) را حذف یا کامنت کنید.
        // Auth::guard('web')->logout();
        // $request->session()->invalidate();
        // $request->session()->regenerateToken();

        // بازگرداندن پاسخ 204 No Content برای نشان دادن موفقیت بدون محتوا
        return response()->noContent();
    }
}
