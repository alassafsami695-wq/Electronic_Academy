<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = ['admin', 'teacher', 'user'];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName]);
        }

        User::firstOrCreate(
            ['email' => 'samialassaf333@gmail.com'],
            [
                'name' => 'Super Admin',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'is_super_admin' => true,
                'is_verified' => true,
            ]
        );

                User::firstOrCreate(
            ['email' => 'sam11@gmail.com'],
            [
                'name' => 'Admin',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'admin')->first()->id,
                'is_super_admin' => false,
                'is_verified' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'teacher@example.com'],
            [
                'name' => 'Teacher User',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'teacher')->first()->id,
                'is_verified' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Normal User',
                'password' => bcrypt('password'),
                'role_id' => Role::where('name', 'user')->first()->id,
                'is_verified' => true,
            ]
        );
    }
}
