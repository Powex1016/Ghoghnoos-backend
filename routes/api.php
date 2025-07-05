<?php
// 🟢 محتوای کامل و نهایی routes/api.php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RatingController;

// روت‌های عمومی (بدون نیاز به لاگین)
Route::get('/bookings/status', [BookingController::class, 'getPublicBookingStatus']);
Route::get('/bookings/times/{date}', [BookingController::class, 'getPublicBookingTimesForDate']);

// روت‌های احراز هویت (ورود، ثبت‌نام، خروج)
require __DIR__.'/auth.php';

// گروه روت‌هایی که نیاز به لاگین دارند
Route::middleware('auth:sanctum')->group(function () {

    // روت برای دریافت اطلاعات کاربر لاگین کرده
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // روت‌های مربوط به پنل کاربری عادی و رزروها
    // GET /api/bookings -> به متد index در کنترلر می‌رود و رزروهای کاربر را برمی‌گرداند
    Route::apiResource('bookings', BookingController::class);
    Route::post('/ratings', [RatingController::class, 'store']);

    // === گروه روت‌های اختصاصی برای ادمین ===
    Route::prefix('admin')->group(function () {
        // آدرس نهایی: /api/admin/bookings
        Route::get('/bookings', [BookingController::class, 'adminIndex']);

        // آدرس نهایی: /api/admin/bookings/{booking}/status
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'adminUpdateStatus']);
    });
});
