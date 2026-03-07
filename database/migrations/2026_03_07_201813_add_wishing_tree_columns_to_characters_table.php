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
            $table->integer('wt_spins')->default(1);
            $table->integer('wt_total_spins')->default(0);
            $table->integer('wt_today_spins')->default(0);
            $table->timestamp('wt_last_spin')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['wt_spins', 'wt_total_spins', 'wt_today_spins', 'wt_last_spin']);
        });
    }
};
