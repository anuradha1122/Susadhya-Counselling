<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 30)
                ->nullable()
                ->after('email');

            $table->string('profile_photo_path')
                ->nullable()
                ->after('phone');

            $table->boolean('is_active')
                ->default(true)
                ->after('profile_photo_path');

            $table->timestamp('last_login_at')
                ->nullable()
                ->after('is_active');

            $table->timestamp('password_changed_at')
                ->nullable()
                ->after('last_login_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'profile_photo_path',
                'is_active',
                'last_login_at',
                'password_changed_at',
            ]);
        });
    }
};
