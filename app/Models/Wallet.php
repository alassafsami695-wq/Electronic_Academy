<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Wallet extends Model {
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'balance',
        'account_number',   
        'wallet_password',
    ];

    protected $hidden = [
        'wallet_password', // إخفاء كلمة المرور عند إرسال البيانات للـ API
    ];

    // لضمان استرجاع الرصيد كقيمة رقمية دقيقة
    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }
}