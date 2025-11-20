<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    // السماح بالحقول القابلة للكتابة جماعياً
    protected $fillable = ['name'];

    /**
     * العلاقة مع المستخدمين: كل دور يمكن أن يكون مرتبط بعدة مستخدمين
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
