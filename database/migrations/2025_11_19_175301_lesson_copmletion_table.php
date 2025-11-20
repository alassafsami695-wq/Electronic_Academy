<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_completion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // ربط بالمستخدم
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade'); // ربط بالدرس
            $table->timestamp('completed_at')->nullable(); // وقت اكتمال الدرس
            $table->unique(['user_id','lesson_id']); // منع التكرار
            $table->timestamps();

            $table->index(['lesson_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_completion');
    }
};
