<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('software_name')->default('ViperPro');
            $table->string('currency_code')->default('USD');
            $table->string('currency_symbol')->default('$');

            $table->decimal('min_deposit', 12, 2)->default(10);
            $table->decimal('max_deposit', 12, 2)->default(10000);
            $table->decimal('min_withdrawal', 12, 2)->default(20);

            $table->boolean('suitpay_is_enable')->default(true);
            $table->boolean('stripe_is_enable')->default(true);
            $table->boolean('disable_spin')->default(false);

            $table->integer('rollover')->default(1);

            $table->string('language_default')->default('en');
            $table->boolean('maintenance_mode')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};