<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('session_id')->nullable();
                $table->string('transaction_id')->nullable()->index();
                $table->string('game')->nullable();
                $table->string('game_uuid')->nullable();
                $table->string('type')->nullable();
                $table->string('type_money')->nullable();
                $table->decimal('amount', 14, 2)->default(0);
                $table->string('providers')->nullable();
                $table->boolean('refunded')->default(false);
                $table->string('round_id')->nullable();
                $table->integer('status')->default(0);
                $table->timestamps();
            });
            return;
        }

        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'session_id')) $table->string('session_id')->nullable();
            if (!Schema::hasColumn('orders', 'transaction_id')) $table->string('transaction_id')->nullable()->index();
            if (!Schema::hasColumn('orders', 'game')) $table->string('game')->nullable();
            if (!Schema::hasColumn('orders', 'game_uuid')) $table->string('game_uuid')->nullable();
            if (!Schema::hasColumn('orders', 'type')) $table->string('type')->nullable();
            if (!Schema::hasColumn('orders', 'type_money')) $table->string('type_money')->nullable();
            if (!Schema::hasColumn('orders', 'amount')) $table->decimal('amount', 14, 2)->default(0);
            if (!Schema::hasColumn('orders', 'providers')) $table->string('providers')->nullable();
            if (!Schema::hasColumn('orders', 'refunded')) $table->boolean('refunded')->default(false);
            if (!Schema::hasColumn('orders', 'round_id')) $table->string('round_id')->nullable();
            if (!Schema::hasColumn('orders', 'status')) $table->integer('status')->default(0);
            if (!Schema::hasColumn('orders', 'created_at') || !Schema::hasColumn('orders', 'updated_at')) $table->timestamps();
        });
    }

    public function down(): void
    {
        // non-destructive rollback intentionally omitted
    }
};
