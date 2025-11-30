<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;

    //---------------- الحقول القابلة للتعبئة -----------------
    protected $fillable = [
        'title',        
        'description',  
        'salary',       
    ];
}
