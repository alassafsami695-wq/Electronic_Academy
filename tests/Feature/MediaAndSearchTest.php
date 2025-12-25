<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Path;
use App\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaAndSearchTest extends TestCase
{
    use RefreshDatabase;

    private $teacherRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherRole = Role::where('name', 'teacher')->first() ?? Role::factory()->create(['name' => 'teacher']);
    }

    /** 1. اختبار رفع صورة عند إنشاء كورس */
    /** 1. اختبار رفع صورة عند إنشاء كورس - نسخة محدثة */
    public function test_teacher_can_upload_course_photo()
    {
        Storage::fake('public'); 

        // 1. إنشاء المدرس مع تفعيل الحساب وتأكيد البريد
        $teacher = User::factory()->create([
            'role_id' => $this->teacherRole->id,
            'status' => 'active',
            'is_verified' => true,
            'email_verified_at' => now(),
        ]);

        // 2. إنشاء بروفايل للمدرس (لأن بعض العلاقات قد تطلبه)
        $teacher->teacherProfile()->create([
            'address' => 'Test Address',
            'phone_number' => '123456789'
        ]);

        $path = Path::factory()->create();

        $courseData = [
            'title' => 'Course with Image',
            'description' => 'Test description',
            'price' => 100,
            'path_id' => $path->id,
            'photo' => UploadedFile::fake()->image('course.jpg')
        ];

        // 3. محاولة الإرسال وتوقع 200 أو 201
        $response = $this->actingAs($teacher, 'sanctum')
                         ->postJson("/api/teacher/courses", $courseData);

        // إذا كان الكود عندك يرجع 200، سيقبلها الاختبار بفضل assertSuccessful
        $response->assertSuccessful(); 
        
        $course = Course::where('title', 'Course with Image')->first();
        $this->assertNotNull($course->photo);
        Storage::disk('public')->assertExists($course->photo);
    }

    /** 2. اختبار البحث عن الكورسات بالاسم */
    public function test_user_can_search_courses_by_title()
    {
        $path = Path::factory()->create();
        Course::factory()->create(['title' => 'Laravel Masterclass', 'path_id' => $path->id]);
        Course::factory()->create(['title' => 'Python for Beginners', 'path_id' => $path->id]);

        // البحث عن "Laravel"
        $response = $this->getJson("/api/courses?search=Laravel");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'Laravel Masterclass'])
                 ->assertJsonMissing(['title' => 'Python for Beginners']);
    }

    /** 3. اختبار فلترة الكورسات حسب المسار (Path) */
    public function test_user_can_filter_courses_by_path()
    {
        $pathProgramming = Path::factory()->create(['title' => 'Programming']);
        $pathDesign = Path::factory()->create(['title' => 'Design']);

        Course::factory()->create(['title' => 'PHP Course', 'path_id' => $pathProgramming->id]);
        Course::factory()->create(['title' => 'UI/UX Course', 'path_id' => $pathDesign->id]);

        // طلب كورسات البرمجة فقط
        $response = $this->getJson("/api/paths/{$pathProgramming->id}/courses");

        $response->assertStatus(200)
                 ->assertJsonFragment(['title' => 'PHP Course'])
                 ->assertJsonMissing(['title' => 'UI/UX Course']);
    }
}