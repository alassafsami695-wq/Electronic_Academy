<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('lesson_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->text('body');
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('set null');
            $table->index('lesson_id');
            $table->index('parent_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('comments');
    }
};
