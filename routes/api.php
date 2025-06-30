<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\BookingController; // اضافه شده: کنترلر رزروها

Route::get('/bookings/status', [BookingController::class, 'getPublicBookingStatus']);
Route::get('/bookings/times/{date}', [BookingController::class, 'getPublicBookingTimesForDate']);

// روت‌های احراز هویت از Laravel Breeze (شامل login, register, logout)
require __DIR__.'/auth.php';

// روت برای دریافت اطلاعات کاربر احراز هویت شده (نیاز به توکن دارد)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// روت‌های API برای مدیریت رزروها
// این گروه از روت‌ها نیاز به احراز هویت با توکن Sanctum دارند.
Route::middleware('auth:sanctum')->group(function () {
    // Route::apiResource به طور خودکار روت‌های RESTful زیر را برای BookingController ایجاد می‌کند:
    // GET      /api/bookings           (متد index: نمایش لیست تمام رزروها)
    // POST     /api/bookings           (متد store: ایجاد یک رزرو جدید)
    // GET      /api/bookings/{booking} (متد show: نمایش جزئیات یک رزرو خاص)
    // PUT/PATCH /api/bookings/{booking} (متد update: به‌روزرسانی یک رزرو)
    // DELETE   /api/bookings/{booking} (متد destroy: حذف یک رزرو)
    Route::apiResource('bookings', BookingController::class);
});
