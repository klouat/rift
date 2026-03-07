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
            // Stats
            $table->bigInteger('xp')->default(0);
            $table->integer('rank')->default(1);
            $table->integer('hp')->default(100);
            $table->integer('cp')->default(100);
            $table->integer('tp')->default(0);
            $table->integer('agility')->default(5);
            $table->integer('dodge')->default(5);
            $table->integer('critical')->default(5);
            $table->integer('purify')->default(0);

            // Equipped Slots
            $table->string('equipped_weapon')->default('wpn_01');
            $table->string('equipped_back_item')->default('back_01');
            $table->string('equipped_accessory')->nullable();
            $table->string('equipped_clothing')->default('set_01');
            $table->string('equipped_hairstyle')->default('hair_01');
            $table->string('equipped_skills')->nullable();

            // JSON Inventories (MySQL Handles this properly)
            $table->json('char_weapons')->nullable();
            $table->json('char_pet_weapons')->nullable();
            $table->json('char_back_items')->nullable();
            $table->json('char_pet_back_items')->nullable();
            $table->json('char_accessories')->nullable();
            $table->json('char_sets')->nullable();
            $table->json('char_hairs')->nullable();
            $table->json('char_skills')->nullable();
            $table->json('char_materials')->nullable();
            $table->json('char_essentials')->nullable();
            $table->string('char_talent_skills')->nullable();
            $table->string('char_senjutsu_skills')->nullable();

            // Extra details for the character selection screen
            $table->string('character_class')->default('');
            $table->integer('village_id')->default(0);
            
            // Skill Elements points
            $table->integer('atrrib_wind')->default(0);
            $table->integer('atrrib_fire')->default(0);
            $table->integer('atrrib_lightning')->default(0);
            $table->integer('atrrib_water')->default(0);
            $table->integer('atrrib_earth')->default(0);
            $table->integer('atrrib_free')->default(0);
        });

        // Add account columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->integer('account_type')->default(0); // 0 = Free, 1 = Emblem, 2 = Emblem+
            $table->integer('tokens')->default(0);
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
