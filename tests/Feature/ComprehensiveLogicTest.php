<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Role;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComprehensiveLogicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'teacher']);
    }

    /** 1. حماية المحتوى المدفوع **/
    public function test_unsubscribed_student_cannot_access_lessons()
    {
        $course = Course::factory()->create();
        $student = User::factory()->create(['is_verified' => true]);

        // بناءً على ملف الروابط: الوصول للكورس الخاص يتطلب اشتراك
        $response = $this->actingAs($student, 'sanctum')
                         ->getJson("/api/courses/{$course->id}");

        // إذا كان النظام يمنع غير المشتركين، يجب أن يعيد 403
        $response->assertStatus(403);
    }

    /** 2. نظام التعليقات **/
    public function test_user_can_post_comment_on_lesson()
    {
        $user = User::factory()->create(['is_verified' => true]);
        $lesson = Lesson::factory()->create();

        // بناءً على الخطأ السابق: الحقل المطلوب هو 'body' وليس 'content'
        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/comments", [
                             'lesson_id' => $lesson->id,
                             'body' => 'هذا تعليق تجريبي' 
                         ]);

        $response->assertStatus(201);
    }

    /** 3. تحديث الملف الشخصي **/
    public function test_user_can_update_profile_info()
    {
        $user = User::factory()->create(['is_verified' => true]);
        
        // إنشاء بروفايل للمستخدم لأن الكنترولر يحاول عمل update() على null
        $user->profile()->create([
            'bio' => 'Old Bio',
            'phone' => '123456'
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson("/api/profile/update", [
                             'name' => 'Updated Name',
                             'bio' => 'New Awesome Bio'
                         ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['name' => 'Updated Name']);
    }

    /** 4. تشفير كلمة مرور المحفظة **/
    public function test_wallet_password_is_encrypted()
    {
        $user = User::factory()->create();
        
        // اختبار التشفير عند الحفظ في قاعدة البيانات
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'balance' => 0,
            'wallet_password' => 'password123',
            'account_number' => 'ACC' . rand(100, 999)
        ]);

        $this->assertNotEquals('password123', $wallet->wallet_password);
        $this->assertTrue(\Hash::check('password123', $wallet->wallet_password));
    }
}