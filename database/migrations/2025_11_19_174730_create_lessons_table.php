<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();

            $table->foreignId('course_id')
                  ->constrained()
                  ->onDelete('cascade');

            $table->string('title')->default('بدون عنوان');

            // ترتيب الدرس داخل الكورس
            $table->unsignedInteger('order');

            // مدة الدرس بالدقائق
            $table->unsignedInteger('duration')->default(0);

            // رابط أو مسار الفيديو
            $table->string('video_url')->nullable();

            // محتوى الدرس النصي
            $table->longText('content')->nullable();

            $table->timestamps();

            // ضمان أن ترتيب الدرس داخل نفس الكورس لا يتكرر
            $table->unique(['course_id', 'order']);

            // فهرس على ترتيب الدروس
            $table->index('order');
        });
    }

    public function down(): void {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('lessons');
        Schema::enableForeignKeyConstraints();
    }
};
