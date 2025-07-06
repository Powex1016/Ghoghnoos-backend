<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // این خط، ستون 'role' را بعد از ستون 'password' اضافه می‌کند
            // و مقدار پیش‌فرض آن را 'user' قرار می‌دهد.
            $table->string('role')->default('user')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // این خط، در صورت بازگردانی (rollback) مایگریشن، ستون 'role' را حذف می‌کند.
            $table->dropColumn('role');
        });
    }
};
