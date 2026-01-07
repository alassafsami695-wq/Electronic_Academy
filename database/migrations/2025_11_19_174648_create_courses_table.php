<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();

            $table->string('title');

            $table->text('description')->nullable();

            $table->string('photo')->nullable();

            $table->decimal('price', 8, 2)->default(0);

            // مدة الكورس (يمكن أن تكون مثلاً "3 ساعات" أو "120 دقيقة")
            $table->string('course_duration')->nullable();

            $table->unsignedInteger('number_of_students')->default(0);

            $table->decimal('rating', 3, 2)->default(0);

            // عدد المبيعات لاستخدامه في best-selling
            $table->unsignedInteger('sales_count')->default(0);

            $table->foreignId('teacher_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->foreignId('path_id')
                  ->constrained('paths')
                  ->onDelete('cascade');

            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('courses');
    }
};
