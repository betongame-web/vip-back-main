<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();

            $table->string('type')->nullable();
            $table->string('payment_method')->nullable();

            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('symbol')->nullable();

            $table->string('status')->default('pending');
            $table->string('idTransaction')->nullable()->unique();
            $table->string('proof')->nullable();

            $table->json('payload')->nullable();
            $table->text('description')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deposits');
    }
};