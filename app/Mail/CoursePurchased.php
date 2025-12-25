<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Course;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CoursePurchased extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $course;

    public function __construct(User $user, Course $course)
    {
        $this->user = $user;
        $this->course = $course;
    }

    public function build()
    {
        return $this->subject('تأكيد شراء كورس جديد')
                    ->html("<h1>مرحباً {$this->user->name}</h1>
                           <p>لقد تم بنجاح اشتراكك في كورس: <strong>{$this->course->title}</strong></p>
                           <p>نتمنى لك رحلة تعليمية ممتعة.</p>");
    }
}