<?php

namespace Database\Seeders;

use App\Models\Track;
use Illuminate\Database\Seeder;

class TrackSeeder extends Seeder
{
    public function run(): void
    {
        Track::updateOrCreate(
            ['name' => 'Backend'],
            ['tips' => [
                'ابدأ بلغة مثل PHP/Laravel أو Node.js',
                'تعلم قواعد البيانات MySQL/PostgreSQL',
                'افهم REST APIs و Authentication',
                'ابنِ مشروع تسجيل دخول وصلاحيات',
                'ركز على الأمان: validation و hashing و middleware'
            ]]
        );

        Track::updateOrCreate(
            ['name' => 'Frontend'],
            ['tips' => [
                'تعلم HTML و CSS و JavaScript',
                'ابدأ بـ React مع Vite',
                'تعلم إدارة الحالة: Context أو Redux',
                'ركز على قابلية الاستخدام و الأداء',
                'ابنِ واجهة تتصل بـ API للتسجيل/الدخول'
            ]]
        );

        Track::updateOrCreate(
            ['name' => 'AI'],
            ['tips' => [
                'تعلم Python و أساسيات الرياضيات',
                'ابدأ بـ NumPy و Pandas و Scikit-learn',
                'افهم التصنيف والتنبؤ والتجميع',
                'ابنِ مشروع صغير (تصنيف نصوص أو توصية)',
                'انتقل لاحقًا لـ TensorFlow/PyTorch'
            ]]
        );
    }
}
