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
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['hp', 'cp', 'agility', 'dodge', 'critical', 'purify']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->integer('hp')->default(100);
            $table->integer('cp')->default(100);
            $table->integer('agility')->default(5);
            $table->integer('dodge')->default(5);
            $table->integer('critical')->default(5);
            $table->integer('purify')->default(0);
        });
    }
};
