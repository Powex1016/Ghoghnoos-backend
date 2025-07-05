<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class RatingController extends Controller
{
    /**
     * Store a newly created rating in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $booking = \App\Models\Booking::find($request->booking_id);

        // اطمینان حاصل شود که کاربر فقط به رزرو خودش امتیاز می‌دهد
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'شما فقط می‌توانید به رزروهای خودتان امتیاز دهید.'], 403);
        }

        // اطمینان حاصل شود که رزرو تکمیل شده است
        if ($booking->status !== 'completed') {
            return response()->json(['message' => 'فقط می‌توانید به رزروهای تکمیل شده امتیاز دهید.'], 400);
        }

        $rating = Rating::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'booking_id' => $request->booking_id,
            ],
            [
                'rating' => $request->rating,
                'comment' => $request->comment,
            ]
        );

        return response()->json([
            'message' => 'امتیاز شما با موفقیت ثبت شد.',
            'rating' => $rating
        ], 201);
    }
}
