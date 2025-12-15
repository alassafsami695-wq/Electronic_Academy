<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $fillable = [
        'wallet_id',
        'type',         // 'debit' أو 'credit'
        'amount',
        'description',
        'course_id',
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
