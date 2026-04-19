<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('likes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('game_id')->nullable();

            $table->timestamps();

            $table->index('user_id');
            $table->index('game_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('likes');
    }
};