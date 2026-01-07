<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\Path;

class CourseLessonSeeder extends Seeder
{
    public function run(): void
    {
        // 1. تحديد الأستاذ (ID = 3)
        $teacherId = 3;
        if (!User::find($teacherId)) {
            User::factory()->create([
                'id' => $teacherId,
                'role' => 'teacher',
                'name' => 'الأستاذ المسؤول',
            ]);
        }

        // 2. جلب المسارات من قاعدة البيانات (التي أنشأها PathSeeder)
        $pathAI = Path::where('title', 'AI & Machine Learning')->first();
        $pathBackend = Path::where('title', 'Backend Development')->first();
        $pathDesign = Path::where('title', 'Frontend Development')->first(); // لربط كورس التصميم
        $pathCyber = Path::where('title', 'Cyber Security')->first();
        $pathAlgo = Path::where('title', 'Algorithms & Data Structures')->first();

        // مصفوفة البيانات مع ربط كل كورس بـ path_id الخاص به
        $coursesData = [
            [
                'title' => 'دورة الذكاء الاصطناعي',
                'photo' => 'uploads/course/ai.jpg',
                'path_id' => $pathAI->id ?? 1,
                'lessons' => [
                    ['title' => 'مقدمة في الشبكات العصبية', 'video' => 'uploads/lesson/ai1.mp4', 'content' => 'الذكاء الاصطناعي هو محاكاة لعمل العقل البشري باستخدام الخوارزميات.'],
                    ['title' => 'خوارزميات التعلم الآلي', 'video' => 'uploads/lesson/ai2.mp4', 'content' => 'التعلم الآلي هو فرع يسمح للحاسوب بالتعلم من البيانات تلقائياً.'],
                ]
            ],
            [
                'title' => 'دورة التصميم الجرافيكي',
                'photo' => 'uploads/course/design.jpg',
                'path_id' => $pathDesign->id ?? 2,
                'lessons' => [
                    ['title' => 'أساسيات نظرية الألوان', 'video' => 'uploads/lesson/Graphic_Design1.mp4', 'content' => 'الألوان هي العنصر الأساسي في أي تصميم وتؤثر على نفسية المتلقي.'],
                    ['title' => 'مبادئ التكوين البصري', 'video' => 'uploads/lesson/Graphic_Design2.mp4', 'content' => 'التكوين هو ترتيب العناصر داخل مساحة العمل لخلق توازن بصري.'],
                ]
            ],
            [
                'title' => 'دورة التسويق الرقمي',
                'photo' => 'uploads/course/marketing.jpg',
                'path_id' => $pathBackend->id ?? 3, // ربطناه بالباك إند كمثال أو اختر مساراً آخر
                'lessons' => [
                    ['title' => 'استراتيجيات منصات التواصل', 'video' => 'uploads/lesson/Marketing_Course1.mp4', 'content' => 'التسويق يتطلب فهم الجمهور المستهدف واختيار المنصة المناسبة.'],
                    ['title' => 'تحليل البيانات التسويقية', 'video' => 'uploads/lesson/Marketing_Course2.mp4', 'content' => 'البيانات هي المحرك الأساسي لاتخاذ القرارات التسويقية.'],
                ]
            ],
            [
                'title' => 'دورة البرمجة المتقدمة',
                'photo' => 'uploads/course/course.jpg',
                'path_id' => $pathAlgo->id ?? 4,
                'lessons' => [
                    ['title' => 'بناء الأنظمة الموزعة', 'video' => 'uploads/lesson/programer1.mp4', 'content' => 'الأنظمة الموزعة تهدف لزيادة الكفاءة بتوزيع المهام.'],
                    ['title' => 'إدارة قواعد البيانات الضخمة', 'video' => 'uploads/lesson/programer2.mp4', 'content' => 'التعامل مع البيانات الكبيرة يتطلب تقنيات متقدمة للسرعة.'],
                ]
            ],
            [
                'title' => 'دورة هندسة الشبكات',
                'photo' => 'uploads/course/networking.jpg',
                'path_id' => $pathCyber->id ?? 5,
                'lessons' => [
                    ['title' => 'بروتوكولات التوجيه الأساسية', 'video' => 'uploads/lesson/Networking_Course1.mp4', 'content' => 'التوجيه هو عملية اختيار المسار الأفضل لنقل البيانات عبر الشبكة.'],
                    ['title' => 'أمن وحماية الشبكات', 'video' => 'uploads/lesson/Networking_Course2.mp4', 'content' => 'حماية الشبكة تتضمن جدران الحماية وتقنيات التشفير المتقدمة.'],
                ]
            ],
        ];

        foreach ($coursesData as $cData) {
            $course = Course::create([
                'teacher_id' => $teacherId,
                'path_id'    => $cData['path_id'], // استخدام الـ path_id المحدد لكل كورس
                'title'      => $cData['title'],
                'description'=> 'شرح شامل لمحتوى ' . $cData['title'],
                'photo'      => $cData['photo'],
                'price'      => rand(10, 30),
                'course_duration' => '6 ساعات',
            ]);

            foreach ($cData['lessons'] as $index => $lData) {
                Lesson::create([
                    'course_id' => $course->id,
                    'title'     => $lData['title'],
                    'order'     => $index + 1,
                    'video_url' => $lData['video'],
                    'content'   => $lData['content'],
                    'duration'  => rand(10, 20),
                ]);
            }
        }

        $this->command->info('تم ربط الكورسات بمساراتها (Paths) بنجاح!');
    }
}