<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    //---------------- الحقول القابلة للتعبئة -----------------
    protected $fillable = [
        'user_id', // رقم المستخدم المرتبط بالمحفظة
        'balance', // رصيد المحفظة الحالي
    ];

    //---------------- العلاقة مع المستخدم -----------------
    public function user()
    {
        return $this->belongsTo(User::class); // كل محفظة مرتبطة بمستخدم واحد
    }
}
