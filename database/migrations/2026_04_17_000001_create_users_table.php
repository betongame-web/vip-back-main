<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            $table->foreignId('role_id')->nullable();
            $table->string('avatar')->nullable();

            $table->string('name');
            $table->string('last_name')->nullable();

            $table->string('cpf')->nullable();
            $table->string('phone')->nullable();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            $table->string('password');

            $table->boolean('logged_in')->default(false);
            $table->boolean('banned')->default(false);

            $table->foreignId('inviter')->nullable();
            $table->string('inviter_code')->nullable()->unique();

            $table->decimal('affiliate_revenue_share', 10, 2)->default(0);
            $table->decimal('affiliate_revenue_share_fake', 10, 2)->default(0);
            $table->decimal('affiliate_cpa', 10, 2)->default(0);
            $table->decimal('affiliate_baseline', 12, 2)->default(0);

            $table->boolean('is_demo_agent')->default(false);
            $table->boolean('is_admin')->default(false);

            $table->string('language')->default('en');

            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};