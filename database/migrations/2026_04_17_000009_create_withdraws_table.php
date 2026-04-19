<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdraws', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained('wallets')->nullOnDelete();

            $table->string('type')->default('pix');
            $table->string('name')->nullable();

            $table->string('pix_key')->nullable();
            $table->string('pix_type')->nullable();

            $table->decimal('amount', 14, 2)->default(0);
            $table->string('currency')->default('USD');
            $table->string('symbol')->nullable();

            $table->string('status')->default('pending');
            $table->string('proof')->nullable();

            $table->json('payload')->nullable();
            $table->text('description')->nullable();

            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('withdraws');
    }
};