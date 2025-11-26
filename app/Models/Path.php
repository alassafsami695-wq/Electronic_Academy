<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Path extends Model
{
    protected $fillable = [
        'title',
        'description',
        'tips'
    ];

    protected $casts = [
        'tips' => 'array', // مهم حتى تتحول JSON إلى array تلقائياً
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

}