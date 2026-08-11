<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            [
                'email' => env(
                    'SUPER_ADMIN_EMAIL',
                    'admin@susadhya.lk'
                ),
            ],
            [
                'name' => env(
                    'SUPER_ADMIN_NAME',
                    'Susadhya Super Administrator'
                ),
                'phone' => env(
                    'SUPER_ADMIN_PHONE',
                    '0700000000'
                ),
                'password' => Hash::make(
                    env(
                        'SUPER_ADMIN_PASSWORD',
                        'ChangeMe@123'
                    )
                ),
                'email_verified_at' => now(),
                'is_active' => true,
                'password_changed_at' => now(),
            ]
        );

        $user->syncRoles(['super_admin']);
    }
}
