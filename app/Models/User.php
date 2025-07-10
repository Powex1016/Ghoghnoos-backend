<?php

namespace App\Models;

use App\Notifications\FarsiResetPasswordNotification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // اضافه شده: برای استفاده از توکن‌های API

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, // اضافه شده: استفاده از HasApiTokens
        HasFactory,
        Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
    'name',
    'email',
    'phone', // <-- این خط را اضافه کنید
    'password',
    'role', // این فیلد از قبل باید وجود داشته باشد
];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the bookings for the user.
     */
    public function bookings() // این متد جدید را اضافه کنید
    {
        return $this->hasMany(Booking::class);
    }
    /**
 * Send the password reset notification.
 *
 * @param  string  $token
 * @return void
 */
public function sendPasswordResetNotification($token)
{
    $this->notify(new FarsiResetPasswordNotification($token));
}
}
