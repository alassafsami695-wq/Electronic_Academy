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
        // 1. إنشاء الأدوار
        $roles = ['admin', 'teacher', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // دالة مساعدة لإنشاء المحفظة
        $createWallet = function ($user) {
            $user->wallet()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'balance' => 100.00,
                    'account_number' => 'SHAM-' . rand(100000, 999999),
                    'wallet_password' => bcrypt('1234'), 
                ]
            );
        };

        // ---------------- Super Admin ----------------
        $superAdmin = User::updateOrCreate(
            ['email' => 'samialassaf333@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'is_super_admin' => true,
                'is_verified' => true,
            ]
        );
        // نستخدم updateOrCreate لضمان تحديث المسار لو كان خطأ
        $superAdmin->profile()->updateOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'photo' => 'uploads/users/admin.jpg', // مسار صافي بدون http
                'phone_number' => '0912345678'
            ]
        );
        $createWallet($superAdmin);

        // ---------------- Admin ----------------
        $admin = User::updateOrCreate(
            ['email' => 'sam11@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'is_super_admin' => false,
                'is_verified' => true,
            ]
        );
        $admin->profile()->updateOrCreate(
            ['user_id' => $admin->id],
            [
                'photo' => 'uploads/users/admin_sub.jpg',
                'phone_number' => '0912345679'
            ]
        );
        $createWallet($admin);

        // ---------------- Teacher ----------------
        $teacher = User::updateOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'teacher')->first()->id,
                'is_verified' => true,
            ]
        );
        $teacher->profile()->updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'photo' => 'uploads/users/teacher.jpg'
            ]
        );
        $teacher->teacherProfile()->updateOrCreate(
            ['user_id' => $teacher->id],
            [
                'photo' => 'uploads/users/teacher.jpg',
                'facebook_url' => 'https://facebook.com/teacher'
            ]
        );
        $createWallet($teacher);

        // ---------------- Normal User ----------------
        $user = User::updateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Normal User',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'user')->first()->id,
                'is_verified' => true,
            ]
        );
        $user->profile()->updateOrCreate(
            ['user_id' => $user->id],
            [
                'photo' => 'uploads/users/student.jpg'
            ]
        );
        $createWallet($user);
    }
}