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
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->integer('gender')->default(0); // 0 = male, 1 = female
            $table->integer('element')->default(1);
            $table->string('hair_style_color')->default('0|0');
            $table->integer('hair_num')->default(1);
            $table->integer('skin_color')->default(16173743);
            $table->integer('level')->default(1);
            $table->integer('gold')->default(0);
            $table->integer('profile_pic')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
