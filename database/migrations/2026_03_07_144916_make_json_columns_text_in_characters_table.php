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
            $table->longText('char_weapons')->nullable()->change();
            $table->longText('char_pet_weapons')->nullable()->change();
            $table->longText('char_back_items')->nullable()->change();
            $table->longText('char_pet_back_items')->nullable()->change();
            $table->longText('char_accessories')->nullable()->change();
            $table->longText('char_sets')->nullable()->change();
            $table->longText('char_hairs')->nullable()->change();
            $table->longText('char_skills')->nullable()->change();
            $table->longText('char_materials')->nullable()->change();
            $table->longText('char_essentials')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            //
        });
    }
};
