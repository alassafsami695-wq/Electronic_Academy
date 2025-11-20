<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();

            // يجب أن تكون الجداول users و lessons أنشئت قبل هذا الملف
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');

            // parent_id بدون constrained() لأنها مربوطة بنفس الجدول قبل إنشاءه بالكامل
            $table->unsignedBigInteger('parent_id')->nullable();

            $table->text('body');
            $table->timestamps();

           // $table->unique(['user_id','lesson_id','body']);

            $table->foreign('parent_id')
                ->references('id')->on('comments')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
