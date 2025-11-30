<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Comment;

class NewCommentNotification extends Notification implements ShouldQueue
{
    use Queueable;

    //---------------- التعليق المرتبط بالإشعار -----------------
    protected Comment $comment;

    //---------------- تهيئة الإشعار مع التعليق -----------------
    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    //---------------- قنوات الإشعار -----------------
    public function via($notifiable): array
    {
        // هذا الإشعار يتم إرساله عبر البريد الإلكتروني فقط
        return ['mail'];
    }

    //---------------- تمثيل الإشعار بالبريد الإلكتروني -----------------
    public function toMail($notifiable): MailMessage
    {
        // جلب عنوان الكورس والدرس المرتبط بالتعليق
        $courseTitle = $this->comment->lesson->course->title;
        $lessonTitle = $this->comment->lesson->title;

        // توليد رابط مباشر للتعليق داخل صفحة الدرس
        $url = url('/teacher/course/' . 
            $this->comment->lesson->course_id . 
            '/lesson/' . $this->comment->lesson_id . 
            '#comment-' . $this->comment->id
        );

        // إعداد رسالة البريد الإلكتروني
        return (new MailMessage)
            ->subject("سؤال جديد على الكورس: {$courseTitle}") 
            ->greeting("مرحبا أستاذ {$notifiable->name},")    
            ->line("تم إضافة سؤال جديد على الدرس '{$lessonTitle}' ضمن الكورس '{$courseTitle}'.") 
            ->line('التعليق: ' . $this->comment->body)         
            ->action('الرد مباشرة على التعليق', $url)     
            ->line('يرجى الرد في أقرب وقت ممكن.');             
    }

    //---------------- تمثيل الإشعار كمصفوفة  -----------------
    public function toArray($notifiable): array
    {
        //  استخدام هذه البيانات لتخزين الإشعار في قاعدة البيانات أو API
        return [
            'comment_id' => $this->comment->id,
            'lesson_id'  => $this->comment->lesson_id,
            'course_id'  => $this->comment->lesson->course_id,
            'body'       => $this->comment->body,
        ];
    }
}
