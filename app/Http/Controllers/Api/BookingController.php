<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * [PUBLIC] Get the booking status for a date range.
     * این متد عمومی است و نیازی به احراز هویت ندارد.
     */
    public function getPublicBookingStatus(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $bookings = Booking::whereBetween('booking_date', [$request->start_date, $request->end_date])
            ->select('booking_date', DB::raw('count(*) as total'))
            ->groupBy('booking_date')
            ->get()
            ->keyBy('booking_date')
            ->map(function ($item) {
                return $item->total;
            });

        return response()->json($bookings);
    }

    /**
     * [PUBLIC] Get booked time slots for a specific date.
     * این متد عمومی است و نیازی به احراز هویت ندارد.
     */
    public function getPublicBookingTimesForDate(Request $request, $date): JsonResponse
    {
        try {
            // اعتبارسنجی فرمت تاریخ
            $validatedDate = Carbon::parse($date)->toDateString();
        } catch (\Exception $e) {
            return response()->json(['message' => 'فرمت تاریخ نامعتبر است.'], 400);
        }

        $bookedTimes = Booking::where('booking_date', $validatedDate)
            ->pluck('booking_time')
            ->map(function ($time) {
                // اطمینان از فرمت زمان به صورت HH:MM
                return Carbon::parse($time)->format('H:i');
            })
            ->all();

        return response()->json($bookedTimes);
    }

    /**
     * Display a listing of the resource for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        // ۱. دریافت تمام رزروهای کاربر، از جدیدترین به قدیمی‌ترین
        $bookings = Auth::user()->bookings()->latest()->get();

        // ۲. فیلتر کردن رزروها برای حذف موارد تکراری بر اساس نوع سرویس، تاریخ و ساعت
        $uniqueBookings = $bookings->unique(function ($item) {
            return $item['service_type'] . $item['booking_date'] . $item['booking_time'];
        });

        // ۳. بازگرداندن لیست منحصر به فرد به فرانت‌اند
        // متد values()->all() کلیدهای آرایه را بازنشانی می‌کند تا ساختار JSON صحیح باشد
        return response()->json($uniqueBookings->values()->all());
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'service_type' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // اضافه کردن وضعیت 'pending' به صورت صریح
        $validatedData['status'] = 'pending';

        $booking = Auth::user()->bookings()->create($validatedData);

        return response()->json(['message' => 'نوبت شما با موفقیت رزرو شد و در انتظار تایید است.', 'booking' => $booking], 201);
    }

    /**
     * [ADMIN] Display a listing of all bookings for the admin panel.
     */
    public function adminIndex(): JsonResponse
    {
        // ۱. دریافت تمام رزروها به همراه اطلاعات کاربر، مرتب شده بر اساس جدیدترین
        $allBookings = Booking::with('user:id,name,email,phone')->latest()->get();

        // ۲. *** کد جدید و کلیدی برای حذف موارد تکراری ***
        // از کلکسیون لاراول برای گروه‌بندی رزروها بر اساس یک شناسه منحصر به فرد استفاده می‌کنیم.
        // این شناسه از ترکیب شناسه‌ی کاربر، نوع سرویس، تاریخ و ساعت ساخته می‌شود.
        $uniqueBookings = $allBookings->unique(function ($item) {
            // اگر کاربر وجود داشته باشد، از ID او استفاده کن، در غیر این صورت یک مقدار خالی قرار بده
            $userId = $item->user ? $item->user->id : '';
            return $userId . $item->service_type . $item->booking_date . $item->booking_time;
        });

        // ۳. بازگرداندن لیست منحصر به فرد به فرانت‌اند
        // متد values() کلیدهای آرایه را ریست می‌کند تا یک آرایه JSON استاندارد داشته باشیم.
        return response()->json($uniqueBookings->values());
    }


    /**
     * [ADMIN] Update the status of a specified booking.
     */
    public function adminUpdateStatus(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'status' => 'required|string|in:pending,confirmed,completed,cancelled',
        ]);

        $booking->status = $request->status;
        $booking->save();

        return response()->json([
            'message' => 'وضعیت رزرو با موفقیت به‌روزرسانی شد.',
            'booking' => $booking,
        ]);
    }
}
