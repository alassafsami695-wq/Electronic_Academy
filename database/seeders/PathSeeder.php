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
                'tips' => json_encode([
                    "ابدأ بتعلم أساسيات PHP",
                    "تعلم قواعد البيانات مثل MySQL",
                    "تعلم Laravel لأتمتة العمليات",
                    "قم ببناء REST APIs لتطبيقات React",
                    "استخدم Postman لاختبار واجهاتك"
                ]),
            ],
            
            [
                'title' => 'Frontend Development',
                'description' => 'Learn client-side development with HTML, CSS, JavaScript, React, and UI frameworks.',
                'tips' => json_encode([
                    "ابدأ بتعلم HTML و CSS",
                    "تعلم JavaScript جيدًا",
                    "تعلم React لإنشاء تطبيقات حديثة",
                    "تعرف على مكتبات UI مثل Tailwind أو Bootstrap",
                    "جرب مشاريع صغيرة لتطبيق ما تعلمته"
                ]),
            ],
            [
                'title' => 'AI & Machine Learning',
                'description' => 'Learn AI concepts, machine learning algorithms, and practical projects.',
                'tips' => json_encode([
                    "ابدأ بأساسيات Python",
                    "تعلم مكتبات Machine Learning مثل scikit-learn و TensorFlow",
                    "حل مسائل عملية لتطبيق الخوارزميات",
                    "اقرأ عن الشبكات العصبية Deep Learning",
                    "جرب Kaggle لمشاريع جاهزة"
                ]),
            ],
        ];

        foreach ($paths as $path) {
            Path::create($path);
        }
    }
}
