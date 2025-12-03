<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('lessons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->string('title')->default('بدون عنوان');
            $table->unsignedInteger('order');
            $table->string('video_url')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'order']);
            $table->index('order');
        });
    }

    public function down(): void {
        Schema::dropIfExists('lessons');
    }
};
