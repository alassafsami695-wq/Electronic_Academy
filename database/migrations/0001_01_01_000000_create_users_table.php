<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    //---------------- تنفيذ عملية إنشاء الجدول -----------------
    public function up(): void {
        Schema::create('users', function (Blueprint $table) {

            //---------------- المفتاح الأساسي -----------------
            $table->id(); 

            //---------------- معلومات الحساب الأساسية -----------------
            $table->string('name'); 
            $table->string('email')->unique(); 
            $table->timestamp('email_verified_at')->nullable(); 
            $table->string('password'); 

            //---------------- التحقق من الحساب -----------------
            $table->boolean('is_verified')->default(false);
            $table->string('email_verification_code')->nullable(); 

            //---------------- صلاحيات خاصة -----------------
            $table->boolean('is_super_admin')->default(false);

            //---------------- تذكر الجلسة -----------------
            $table->rememberToken(); 

            //---------------- الطوابع الزمنية -----------------
            $table->timestamps(); 
        });
    }

            //---------------- التراجع عن الإنشاء (الحذف) -----------------
        public function down(): void
        {
            Schema::disableForeignKeyConstraints();
            Schema::dropIfExists('users');
            Schema::enableForeignKeyConstraints();
        }

};
