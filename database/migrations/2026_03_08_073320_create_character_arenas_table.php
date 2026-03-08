<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('character_arenas', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->unsignedBigInteger('char_id')->unique();
            $blueprint->integer('stamina')->default(100);
            $blueprint->integer('max_stamina')->default(100);
            $blueprint->integer('trophies')->default(0);
            $blueprint->integer('enemy_id')->default(-1);
            $blueprint->integer('first_open')->default(1);
            $blueprint->integer('village_changed')->default(0);
            $blueprint->text('claimed_trophy_rewards')->nullable();
            $blueprint->timestamp('last_stamina_reset')->nullable();
            $blueprint->timestamps();

            $blueprint->foreign('char_id')->references('id')->on('characters')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_arenas');
    }
};
