<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unique(['course_id', 'user_id']);
            $table->timestamps();

            $table->index(['user_id', 'course_id']);
            $table->decimal('grade', 5, 2)->default(0); 
        });
    }

    public function down(): void {
        Schema::dropIfExists('course_user');
    }
};
