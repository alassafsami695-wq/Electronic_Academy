<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'location',
        'phone_primary',
        'phone_secondary',
        'email',
        'whatsapp',
        'map_link'
    ];
}