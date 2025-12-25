<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CoursePurchasedNotification extends Notification
{
    use Queueable;

    public $course;
    public $user;

    /**
     * استقبال بيانات الكورس والطالب عند إنشاء الإشعار
     */
    public function __construct($course, $user)
    {
        $this->course = $course;
        $this->user = $user;
    }

    /**
     * إرسال الإشعار عبر البريد وقاعدة البيانات (لتظهر في المنصة)
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * شكل البريد الإلكتروني
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تأكيد شراء كورس جديد')
            ->greeting("أهلاً {$notifiable->name}")
            ->line("تمت عملية شراء كورس: **{$this->course->title}** بنجاح.")
            ->action('مشاهدة الكورس', url('/courses/' . $this->course->id))
            ->line('شكراً لاستخدامك منصتنا التعليمية!');
    }

    /**
     * البيانات التي ستخزن في قاعدة البيانات (للعرض داخل التطبيق)
     */
    public function toArray(object $notifiable): array
    {
        return [
            'course_id' => $this->course->id,
            'course_title' => $this->course->title,
            'student_name' => $this->user->name,
        ];
    }
}