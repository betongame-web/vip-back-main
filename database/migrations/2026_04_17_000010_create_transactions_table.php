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

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();

            $table->string('type');
            $table->string('reference')->nullable()->unique();

            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('symbol')->nullable();

            $table->string('status')->default('pending');
            $table->decimal('balance_before', 14, 2)->nullable();
            $table->decimal('balance_after', 14, 2)->nullable();

            $table->text('description')->nullable();
            $table->json('payload')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};