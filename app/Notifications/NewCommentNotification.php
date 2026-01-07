<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Comment;

class NewCommentNotification extends Notification
{
    use Queueable;

    protected Comment $comment;

    public function __construct(Comment $comment)
    {
        $this->comment = $comment;
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $courseTitle = optional($this->comment->lesson->course)->title ?? 'بدون عنوان';
        $lessonTitle = optional($this->comment->lesson)->title ?? 'بدون عنوان';

        $url = url('/teacher/course/' .
            $this->comment->lesson->course_id .
            '/lesson/' . $this->comment->lesson_id .
            '#comment-' . $this->comment->id
        );

        return (new MailMessage)
            ->subject("سؤال جديد على الكورس: {$courseTitle}")
            ->greeting("مرحبًا أستاذ {$notifiable->name},")
            ->line("تم إضافة سؤال جديد على الدرس '{$lessonTitle}' ضمن الكورس '{$courseTitle}'.")
            ->line('نص التعليق: ' . $this->comment->body)
            ->action('الرد مباشرة على التعليق', $url)
            ->line('شكرًا لاستخدامك منصة Electronic Academy!');
    }

    public function toArray($notifiable): array
    {
        return [
            'comment_id' => $this->comment->id,
            'lesson_id'  => $this->comment->lesson_id,
            'course_id'  => $this->comment->lesson->course_id,
            'body'       => $this->comment->body,
        ];
    }
}
