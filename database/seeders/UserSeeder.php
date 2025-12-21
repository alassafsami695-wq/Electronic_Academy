<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Wallet;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // إنشاء الأدوار
        $roles = ['admin', 'teacher', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // دالة مساعدة لإنشاء المحفظة لتقليل تكرار الكود
        $createWallet = function ($user) {
            $user->wallet()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 100.00, // رصيد افتراضي
                    'account_number' => 'SHAM-' . rand(100000, 999999),
                    'wallet_password' => bcrypt('1234'), // كلمة مرور افتراضية للمحفظة
                ]
            );
        };

        // ---------------- Super Admin ----------------
        $superAdmin = User::firstOrCreate(
            ['email' => 'samialassaf333@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'is_super_admin' => true,
                'is_verified' => true,
            ]
        );
        $superAdmin->profile()->firstOrCreate([]);
        $createWallet($superAdmin);

        // ---------------- Admin ----------------
        $admin = User::firstOrCreate(
            ['email' => 'sam11@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'is_super_admin' => false,
                'is_verified' => true,
            ]
        );
        $admin->profile()->firstOrCreate([]);
        $createWallet($admin);

        // ---------------- Teacher ----------------
        $teacher = User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'teacher')->first()->id,
                'is_verified' => true,
            ]
        );
        $teacher->profile()->firstOrCreate([]);
        $createWallet($teacher);

        // ---------------- Normal User ----------------
        $user = User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Normal User',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'user')->first()->id,
                'is_verified' => true,
            ]
        );
        $user->profile()->firstOrCreate([]);
        $createWallet($user);
    }
}