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
        Schema::create('character_hunting_house', function (Blueprint $table) {
            $table->id();
            $table->foreignId('char_id')->constrained('characters')->onDelete('cascade');
            $table->text('attempts')->nullable(); // Comma-separated or JSON
            $table->date('last_reset')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_hunting_house');
    }
};
