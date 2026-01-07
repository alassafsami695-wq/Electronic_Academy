<?php

namespace Database\Factories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
  public function definition(): array
{
    return [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'email_verified_at' => now(), // اجعلها مفعلة تلقائياً للاختبارات
        'password' => bcrypt('password123'),
        'role_id' => \App\Models\Role::firstOrCreate(['name' => 'student'])->id,
        'status' => 'active',
    ];
}
}