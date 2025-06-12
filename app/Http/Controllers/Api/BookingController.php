<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // برای دسترسی به کاربر احراز هویت شده
use Illuminate\Validation\ValidationException; // برای مدیریت خطاهای اعتبارسنجی
use Illuminate\Http\JsonResponse; // برای بازگشت پاسخ JSON

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     * نمایش لیست تمام رزروها برای کاربر احراز هویت شده.
     * می‌تواند بر اساس تاریخ فیلتر شود.
     */
    public function index(Request $request): JsonResponse
    {
        // فقط رزروهای مربوط به کاربر فعلی را برمی‌گرداند
        $bookings = Auth::user()->bookings();

        // فیلتر بر اساس تاریخ اگر پارامترهای start_date, end_date یا booking_date وجود دارند
        if ($request->has('start_date')) {
            $bookings->whereDate('booking_date', '>=', $request->start_date);
        }

        if ($request->has('end_date')) {
            $bookings->whereDate('booking_date', '<=', $request->end_date);
        }

        if ($request->has('booking_date')) {
            $bookings->whereDate('booking_date', $request->booking_date);
        }

        $bookings = $bookings->latest()->get(); // دریافت رزروها و مرتب‌سازی از جدیدترین به قدیمی‌ترین

        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     * ذخیره یک رزرو جدید.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            // اعتبارسنجی ورودی‌ها
            $request->validate([
                'booking_date' => 'required|date|after_or_equal:today', // تاریخ باید امروز یا بعد از آن باشد
                'booking_time' => 'required|date_format:H:i', // ساعت باید فرمت HH:MM داشته باشد
                'service_type' => 'required|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            // بررسی تکراری نبودن رزرو در آن زمان و تاریخ برای همان کاربر
            $existingBooking = Booking::where('user_id', Auth::id())
                                    ->where('booking_date', $request->booking_date)
                                    ->where('booking_time', $request->booking_time)
                                    ->first();

            if ($existingBooking) {
                return response()->json([
                    'message' => 'شما قبلاً در این تاریخ و ساعت نوبت رزرو کرده‌اید.',
                ], 409); // Conflict - نشان‌دهنده تکراری بودن منبع است
            }

            // ایجاد رزرو جدید
            $booking = Auth::user()->bookings()->create([
                'booking_date' => $request->booking_date,
                'booking_time' => $request->booking_time,
                'service_type' => $request->service_type,
                'notes' => $request->notes,
                'status' => 'pending', // وضعیت پیش‌فرض 'pending' (در انتظار تایید)
            ]);

            return response()->json([
                'message' => 'نوبت شما با موفقیت رزرو شد.',
                'booking' => $booking,
            ], 201); // Created - نشان‌دهنده ایجاد موفقیت‌آمیز منبع است
        } catch (ValidationException $e) {
            // مدیریت خطاهای اعتبارسنجی
            return response()->json([
                'message' => 'خطا در اعتبارسنجی ورودی‌ها.',
                'errors' => $e->errors(),
            ], 422); // Unprocessable Entity - نشان‌دهنده خطاهای اعتبارسنجی است
        } catch (\Exception $e) {
            // مدیریت سایر خطاها (خطاهای سرور)
            return response()->json([
                'message' => 'خطایی در سرور رخ داد.',
                'error' => $e->getMessage(),
            ], 500); // Internal Server Error
        }
    }

    /**
     * Display the specified resource.
     * نمایش جزئیات یک رزرو خاص.
     */
    public function show(Booking $booking): JsonResponse
    {
        // اطمینان حاصل می‌کند که فقط کاربر صاحب رزرو می‌تواند آن را ببیند
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'شما اجازه دسترسی به این رزرو را ندارید.'], 403); // Forbidden
        }

        return response()->json($booking);
    }

    /**
     * Update the specified resource in storage.
     * به‌روزرسانی یک رزرو.
     */
    public function update(Request $request, Booking $booking): JsonResponse
    {
        // اطمینان حاصل می‌کند که فقط کاربر صاحب رزرو می‌تواند آن را به‌روزرسانی کند
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'شما اجازه به‌روزرسانی این رزرو را ندارید.'], 403);
        }

        try {
            $request->validate([
                'booking_date' => 'sometimes|required|date|after_or_equal:today', // sometimes: فقط اگر فیلد ارسال شد، الزامی و اعتبارسنجی کن
                'booking_time' => 'sometimes|required|date_format:H:i',
                'service_type' => 'sometimes|required|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'status' => 'sometimes|required|string|in:pending,confirmed,cancelled', // فقط این وضعیت‌ها مجاز هستند
            ]);

            // بررسی تکراری نبودن رزرو جدید (در صورت تغییر تاریخ یا ساعت)
            if ($request->has('booking_date') || $request->has('booking_time')) {
                $existingBooking = Booking::where('user_id', Auth::id())
                                        ->where('booking_date', $request->booking_date ?? $booking->booking_date) // اگر تاریخ در درخواست هست از آن استفاده کن وگرنه از تاریخ فعلی رزرو
                                        ->where('booking_time', $request->booking_time ?? $booking->booking_time) // اگر ساعت در درخواست هست از آن استفاده کن وگرنه از ساعت فعلی رزرو
                                        ->where('id', '!=', $booking->id) // مطمئن شویم که خود رزرو فعلی را با خودش مقایسه نمی‌کنیم
                                        ->first();

                if ($existingBooking) {
                    return response()->json([
                        'message' => 'این تاریخ و ساعت برای شما قبلاً رزرو شده است.',
                    ], 409);
                }
            }

            $booking->update($request->all()); // به‌روزرسانی رزرو با تمام داده‌های درخواست

            return response()->json([
                'message' => 'رزرو با موفقیت به‌روزرسانی شد.',
                'booking' => $booking,
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'خطا در اعتبارسنجی ورودی‌ها.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'خطایی در سرور رخ داد.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     * حذف یک رزرو.
     */
    public function destroy(Booking $booking): JsonResponse
    {
        // اطمینان حاصل می‌کند که فقط کاربر صاحب رزرو می‌تواند آن را حذف کند
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'شما اجازه حذف این رزرو را ندارید.'], 403);
        }

        $booking->delete(); // حذف رزرو

        return response()->json(['message' => 'رزرو با موفقیت حذف شد.']); // می‌توان از noContent() هم استفاده کرد (204)
    }
}
