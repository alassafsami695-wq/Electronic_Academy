<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('company_name')->nullable(); // اسم الشركة
            $table->string('company_email')->nullable(); // إيميل الشركة
            $table->string('job_type')->default('full-time'); // نوع العمل
            $table->integer('working_hours')->nullable(); // ساعات الدوام
            $table->decimal('salary', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};