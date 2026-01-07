<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory; 

class Path extends Model
{
    use HasFactory;
    protected $fillable = ['title', 'description', 'photo'];

    protected $casts = [
        'tips' => 'array',
    ];

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    protected function photo(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn ($value) => $value ? asset($value) : null,
        );
    }
}
