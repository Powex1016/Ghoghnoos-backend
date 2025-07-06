<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
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
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
                // *** ولیدیشن برای شماره تلفن اضافه شد ***
                'phone' => ['required', 'string', 'unique:users,phone', 'size:11'],
                'password' => ['required', 'confirmed', Rules\Password::defaults()],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'اطلاعات ارسالی نامعتبر است.',
                'errors' => $e->errors()
            ], 422);
        }

        // *** شماره تلفن به ایجاد کاربر اضافه شد ***
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone, // ذخیره شماره تلفن
            'password' => Hash::make($request->string('password')),
        ]);

        event(new Registered($user));

        Auth::login($user);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
            'message' => 'ثبت نام با موفقیت انجام شد و شما وارد شدید.'
        ], 201);
    }
}
