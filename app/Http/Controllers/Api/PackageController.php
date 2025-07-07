<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\Booking; // <-- ایمپورت کردن مدل Booking
use Carbon\Carbon;     // <-- ایمپورت کردن Carbon برای کار با تاریخ و زمان

class PackageController extends Controller
{
    /**
     * Handle a package purchase request.
     */
    public function purchase(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_name' => 'required|string',
            'package_price' => 'required|string',
        ]);

        $user = Auth::user();

        // ایجاد یک رکورد جدید در جدول bookings برای ثبت خرید پکیج
        Booking::create([
            'user_id' => $user->id,
            'booking_date' => Carbon::now()->toDateString(),
            'booking_time' => Carbon::now()->toTimeString(),
            'service_type' => $validated['package_name'], // نام پکیج به عنوان نوع سرویس
            'notes' => 'خرید پکیج با مبلغ ' . $validated['package_price'], // یادداشت برای مشخص شدن خرید
            'status' => 'completed', // وضعیت روی 'تکمیل شده' تنظیم می‌شود
        ]);

        // برگرداندن پیام موفقیت‌آمیز
        return response()->json([
            'message' => 'خرید پکیج شما با موفقیت ثبت و نهایی شد.'
        ], 200); // 200 OK
    }
}
