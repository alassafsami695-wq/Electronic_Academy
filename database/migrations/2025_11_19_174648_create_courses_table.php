<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('path_id')->constrained()->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('users')->onDelete('cascade');
            $table->string('title');
            $table->longText('summary');
            $table->decimal('price', 8, 2);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['teacher_id', 'title']);
            $table->index('path_id');
            $table->index('teacher_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('courses');
    }
};
    