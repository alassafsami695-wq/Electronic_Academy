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
                'photo' => 'uploads/paths/backeend.jpg', // لاحظ المسافة كما في الصورة
            ],
            [
                'title' => 'Frontend Development',
                'description' => 'Learn client-side development with HTML, CSS, JavaScript, React, and UI frameworks.',
                'photo' => 'uploads/paths/frontend.png', // الصيغة هنا PNG كما في الصورة
            ],
            [
                'title' => 'AI & Machine Learning',
                'description' => 'Learn AI concepts, machine learning algorithms, and practical projects.',
                'photo' => 'uploads/paths/AI.jpg',
            ],
            [
                'title' => 'Cyber Security',
                'description' => 'Learn ethical hacking, network security, penetration testing, and digital forensics.',
                'photo' => 'uploads/paths/Cybersecurity.jpg', // استخدام الاسم العربي كما في صورتك
            ],
            [
                'title' => 'Algorithms & Data Structures',
                'description' => 'Learn algorithmic thinking, problem solving, data structures, and competitive programming.',
                'photo' => 'uploads/paths/algorithms.jpg', // استخدام الاسم العربي كما في صورتك
            ],
        ];

        foreach ($paths as $path) {
            Path::create($path);
        }
    }
}