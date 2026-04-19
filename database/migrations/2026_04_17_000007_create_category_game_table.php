<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('category_game', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->foreignId('game_id')->constrained('games')->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['category_id', 'game_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('category_game');
    }
};