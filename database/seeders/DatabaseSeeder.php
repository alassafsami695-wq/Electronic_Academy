<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            PathSeeder::class,
            UserSeeder::class,
            TrackSeeder::class,
        ]);
    }
}
