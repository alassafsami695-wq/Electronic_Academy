<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // التأكد من وجود الأدوار قبل إنشاء المستخدمين
        $roles = ['admin', 'teacher', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        // إنشاء مستخدم تجريبي عادي
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role_id' => Role::where('name', 'user')->first()->id,
            'password' => bcrypt('password'),
        ]);

        // إنشاء مشرف رئيسي
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role_id' => Role::where('name', 'admin')->first()->id,
            'password' => bcrypt('password'),
            'is_super_admin' => true,
        ]);

        // إنشاء مدرس تجريبي
        User::create([
            'name' => 'Teacher User',
            'email' => 'teacher@example.com',
            'role_id' => Role::where('name', 'teacher')->first()->id,
            'password' => bcrypt('password'),
        ]);
    }
}
