<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Path;

class PathSeeder extends Seeder
{
    public function run(): void
    {
        $paths = [
            [
                'title' => 'Backend Development',
                'description' => 'Learn server-side development using PHP, Laravel, Node.js, databases, and APIs.',
                'photo' => null,
            ],

            [
                'title' => 'Frontend Development',
                'description' => 'Learn client-side development with HTML, CSS, JavaScript, React, and UI frameworks.',
                'photo' => null,
            ],

            [
                'title' => 'AI & Machine Learning',
                'description' => 'Learn AI concepts, machine learning algorithms, and practical projects.',
                'photo' => null,
            ],

            // -------------------------
            // Cyber Security Track
            // -------------------------
            [
                'title' => 'Cyber Security',
                'description' => 'Learn ethical hacking, network security, penetration testing, and digital forensics.',
                'photo' => null,
            ],

            // -------------------------
            // Algorithms Track
            // -------------------------
            [
                'title' => 'Algorithms & Data Structures',
                'description' => 'Learn algorithmic thinking, problem solving, data structures, and competitive programming.',
                'photo' => null,
            ],
        ];

        foreach ($paths as $path) {
            Path::create($path);
        }
    }
}
