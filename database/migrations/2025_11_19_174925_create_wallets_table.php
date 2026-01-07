<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // إنشاء جدول المحافظ مع الحقول الإضافية للأمان والبيانات البنكية
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->onDelete('cascade');
            $table->decimal('balance', 10, 2)->default(100.00);
            $table->string('account_number')->nullable(); // رقم حساب شام كاش أو البنك
            $table->string('wallet_password')->nullable(); // كلمة مرور المحفظة المشفرة
            $table->timestamps();
        });

        // إنشاء جدول العمليات
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('wallets')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit']); 
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('payment_gateway')->default('ShamCash');
            $table->string('reference_id')->unique()->nullable(); 
            $table->string('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('wallets');
    }
};