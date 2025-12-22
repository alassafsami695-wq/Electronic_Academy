<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('location')->nullable(); // الموقع (العنوان)
            $table->string('phone_primary')->nullable(); // الرقم الأساسي
            $table->string('phone_secondary')->nullable(); // رقم إضافي
            $table->string('email')->nullable(); // البريد الإلكتروني الرسمي
            $table->string('whatsapp')->nullable();
            $table->text('map_link')->nullable(); // رابط خريطة جوجل
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_settings');
    }
};
