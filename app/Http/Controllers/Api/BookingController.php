<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource for the authenticated user.
     */
    // 🟢 بعد از تغییر
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

    // ... سایر متدهای شما مانند show, update, destroy ...

    /**
     * [ADMIN] Display a listing of all bookings for the admin panel.
     */
    public function adminIndex(): JsonResponse
    {
        // نکته: در یک پروژه واقعی، اینجا باید بررسی شود که کاربر لاگین کرده ادمین است یا خیر
        // if (!Auth::user()->isAdmin()) {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $bookings = Booking::with('user:id,name,email')->latest()->get();

        return response()->json($bookings);
    }

    /**
     * [ADMIN] Update the status of a specified booking.
     */
    public function adminUpdateStatus(Request $request, Booking $booking): JsonResponse
    {
        // اینجا هم باید دسترسی ادمین چک شود

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
