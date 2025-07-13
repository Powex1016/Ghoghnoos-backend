<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\SearchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// =================================================================
// 1. روت‌های عمومی (بدون نیاز به لاگین)
// =================================================================
Route::get('/bookings/status', [BookingController::class, 'getPublicBookingStatus']);
Route::get('/bookings/times/{date}', [BookingController::class, 'getPublicBookingTimesForDate']);
Route::get('/search', [SearchController::class, 'search']);


// =================================================================
// 2. روت‌های احراز هویت (ورود، ثبت‌نام و ...)
// =================================================================
require __DIR__.'/auth.php';


// =================================================================
// 3. روت‌های محافظت شده (نیاز به لاگین دارند)
// =================================================================
Route::middleware('auth:sanctum')->group(function () {

    // روت برای دریافت اطلاعات کاربر لاگین کرده
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // روت‌های مربوط به پنل کاربری عادی و رزروها
    Route::apiResource('bookings', BookingController::class);
    Route::post('/ratings', [RatingController::class, 'store']);

    // مسیر برای خرید پکیج
    Route::post('/packages/purchase', [PackageController::class, 'purchase']);

    // === گروه روت‌های اختصاصی برای ادمین ===
    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        // آدرس نهایی: /api/admin/bookings
        Route::get('/bookings', [BookingController::class, 'adminIndex']);

        // آدرس نهایی: /api/admin/bookings/{booking}/status
        Route::patch('/bookings/{booking}/status', [BookingController::class, 'adminUpdateStatus']);
    });
});
