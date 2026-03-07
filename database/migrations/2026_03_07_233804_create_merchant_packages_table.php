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
        Schema::create('merchant_packages', function (Blueprint $table) {
            $table->id();
            $table->string('package_id')->unique();
            $table->json('skills');
            $table->json('advanced_skills')->nullable();
            $table->json('prices');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('merchant_packages');
    }
};
