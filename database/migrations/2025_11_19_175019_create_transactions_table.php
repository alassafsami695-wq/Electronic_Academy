<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained()->onDelete('cascade'); // ربط بالمحفظة
            $table->enum('type', ['credit', 'debit']); // تم تصحيح النوع
            $table->decimal('amount', 10, 2);
            $table->string('description')->nullable();
            $table->foreignId('course_id')->nullable()->constrained()->onDelete('set null'); // ربط بالكورس إذا كانت العملية شراء
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
