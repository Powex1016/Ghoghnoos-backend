<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Booking;

class SearchController extends Controller
{
    /**
     * Handle a search request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json(['data' => []]);
        }

        // جستجو در انواع خدمات (سرویس‌ها)
        $services = Booking::where('service_type', 'LIKE', "%{$query}%")
                            ->distinct()
                            ->pluck('service_type');

        // در اینجا می‌توانید مدل‌های دیگر را نیز جستجو کنید
        // برای مثال: جستجو در نام پکیج‌ها یا هر چیز دیگری

        return response()->json(['data' => $services]);
    }
}
