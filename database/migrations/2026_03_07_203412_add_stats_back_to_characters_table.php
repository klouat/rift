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
            $table->integer('hp')->default(0);
            $table->integer('cp')->default(0);
            $table->integer('agility')->default(0);
            $table->integer('dodge')->default(0);
            $table->integer('critical')->default(0);
            $table->integer('purify')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['hp', 'cp', 'agility', 'dodge', 'critical', 'purify']);
        });
    }
};
