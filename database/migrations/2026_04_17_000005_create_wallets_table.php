<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();

            $table->decimal('balance', 14, 2)->default(0);
            $table->decimal('bonus_balance', 14, 2)->default(0);
            $table->decimal('withdrawable_balance', 14, 2)->default(0);
            $table->decimal('total_balance', 14, 2)->default(0);

            $table->decimal('total_deposited', 14, 2)->default(0);
            $table->decimal('total_withdrawn', 14, 2)->default(0);
            $table->decimal('total_wagered', 14, 2)->default(0);
            $table->decimal('rollover_remaining', 14, 2)->default(0);

            $table->boolean('active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};