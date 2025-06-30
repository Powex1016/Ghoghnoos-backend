<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // مهم: این خط باید باشد
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException; // اضافه شده: برای خطای ولیدیشن دستی
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        // ولیدیشن
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
        } catch (ValidationException $e) {
            // اگر ولیدیشن ناموفق بود، خطاهای JSON را برگردانید
            return response()->json([
                'message' => 'اطلاعات ارسالی نامعتبر است.',
                'errors' => $e->errors()
            ], 422);
        }

        // ایجاد کاربر
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user); // این خط اختیاری است، اما برای Sanctum نیاز به یک توکن اولیه دارید

        // ساخت توکن Sanctum برای کاربر
        $token = $user->createToken('auth_token')->plainTextToken;

        // بازگرداندن پاسخ JSON موفقیت
        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'ثبت نام با موفقیت انجام شد و شما وارد شدید.'
        ], 201); // 201 Created
    }
}
