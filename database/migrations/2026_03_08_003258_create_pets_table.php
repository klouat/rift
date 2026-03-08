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
        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('char_id')->constrained('characters')->onDelete('cascade');
            $table->string('pet_swf');
            $table->string('pet_name')->nullable();
            $table->integer('pet_level')->default(1);
            $table->integer('pet_xp')->default(0);
            $table->boolean('pet_favorite')->default(false);
            $table->integer('pet_mp')->default(0);
            $table->text('pet_skills')->nullable();
            $table->string('pet_weapon')->nullable();
            $table->string('pet_back_item')->nullable();
            $table->integer('pet_emblem')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pets');
    }
};
