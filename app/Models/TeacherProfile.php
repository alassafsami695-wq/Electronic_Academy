<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeacherProfile extends Model
{
    protected $fillable = [
        'user_id',
        'facebook_url',
        'linkedin_url',
        'instagram_url',
        'youtube_url',
        'github_url',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
