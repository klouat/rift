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
            $table->date('ed_tokens_last_claim')->nullable();
            $table->date('ed_xp_last_claim')->nullable();
            $table->date('ed_skills_last_claim')->nullable();
            $table->integer('ed_double_xp')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn([
                'ed_tokens_last_claim',
                'ed_xp_last_claim',
                'ed_skills_last_claim',
                'ed_double_xp'
            ]);
        });
    }
};
