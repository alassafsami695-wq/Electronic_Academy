<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained()->onDelete('cascade'); // ربط بالمسار
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade'); // ربط بالمدرس
            $table->string('title');
            $table->longText('summary');
            $table->decimal('price', 8, 2); // تم تصحيح الاسم
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            // اختياري: منع وجود دورات بنفس الاسم لنفس المدرس
            $table->unique(['teacher_id', 'title']);
            //$table->json('tips')->nullable();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
