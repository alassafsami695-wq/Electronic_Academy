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

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

}