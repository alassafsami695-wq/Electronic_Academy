<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class WalletFactory extends Factory
{
   // database/factories/WalletFactory.php
    public function definition(): array
    {
        return [
            'user_id' => \App\Models\User::factory(),
            'balance' => 0,
            'wallet_password' => \Illuminate\Support\Facades\Hash::make('1234'),
        ];
    }
}