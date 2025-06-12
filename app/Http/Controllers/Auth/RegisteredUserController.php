<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // اضافه شده: برای نوع بازگشتی JSON
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse // تغییر نوع بازگشتی
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        // تغییر اصلی اینجا است: بازگرداندن JSON شامل کاربر و توکن
        return response()->json([
            'user' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken, // ایجاد توکن Sanctum
            'message' => 'ثبت نام با موفقیت انجام شد و شما وارد شدید.'
        ], 201); // 201 Created برای نشان دادن ایجاد موفقیت‌آمیز منبع
    }
}
