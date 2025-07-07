<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB; // <-- این خط اضافه شده
use Carbon\Carbon;                 // <-- این خط اضافه شده

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
        $bookings = Auth::user()->bookings()->latest()->get();
        return response()->json($bookings);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'booking_date' => 'required|date|after_or_equal:today',
            'booking_time' => 'required|date_format:H:i',
            'service_type' => 'required|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        $booking = Auth::user()->bookings()->create($request->all());

        return response()->json(['message' => 'نوبت شما با موفقیت رزرو شد.', 'booking' => $booking], 201);
    }

    /**
     * [ADMIN] Display a listing of all bookings for the admin panel.
     */
    public function adminIndex(): JsonResponse
    {
        $bookings = Booking::with('user:id,name,email,phone')->latest()->get();
        return response()->json($bookings);
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
