<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

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

        //
        // در یک پروژه واقعی، اینجا می‌توانید اطلاعات خرید را در دیتابیس ذخیره کنید.
        // Purchase::create([...]);
        //

        // به جای ارسال لینک پرداخت، یک پیام موفقیت‌آمیز برمی‌گردانیم
        return response()->json([
            'message' => 'خرید پکیج با موفقیت برای شما ثبت شد.'
        ], 200); // 200 OK
    }
}
