<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {

      \App\Models\ContactSetting::create([
    'location' => 'سوريا - دمشق - حي المزة',
    'phone_primary' => '+963 11 1234567',
    'email' => 'info@academy.com',
    'whatsapp' => '+963 999 888 777',
    'map_link' => 'https://googl/maps/example'
    ]);
        $this->call([
           PathSeeder::class,
            UserSeeder::class,
          //  TrackSeeder::class,
          CourseLessonSeeder::class,
        ]);
    }
}
