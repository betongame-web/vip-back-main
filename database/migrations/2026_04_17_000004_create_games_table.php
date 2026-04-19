<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();

            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();

            $table->string('game_code')->unique();
            $table->string('game_name');

            $table->string('slug')->nullable()->unique();
            $table->string('cover')->nullable();

            $table->string('distribution')->default('source');
            $table->string('game_id')->nullable();
            $table->string('game_server_url')->nullable();

            $table->text('description')->nullable();

            $table->boolean('is_featured')->default(false);
            $table->boolean('status')->default(true);

            $table->unsignedBigInteger('views')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};