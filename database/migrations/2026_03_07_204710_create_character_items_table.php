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
        Schema::create('character_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->onDelete('cascade');
            $table->string('item_type', 50);
            $table->string('item_id', 100);
            $table->integer('quantity')->default(1);
            $table->timestamps();
            $table->index(['character_id', 'item_type']);
        });

        foreach (['character_skills', 'character_talent_skills', 'character_senjutsu_skills'] as $tbl) {
            Schema::create($tbl, function (Blueprint $table) {
                $table->id();
                $table->foreignId('character_id')->constrained('characters')->onDelete('cascade');
                $table->string('skill_id', 100);
                $table->integer('quantity')->default(1);
                $table->timestamps();
                $table->index('character_id');
            });
        }

        $types = [
            'char_weapons', 'char_pet_weapons', 'char_back_items', 'char_pet_back_items',
            'char_accessories', 'char_sets', 'char_hairs', 'char_skills', 'char_materials',
            'char_essentials', 'char_talent_skills', 'char_senjutsu_skills'
        ];

        // Migrate current JSON strings to tables
        $characters = \Illuminate\Support\Facades\DB::table('characters')->get();

        foreach ($characters as $char) {
            foreach ($types as $type) {
                if (!empty($char->$type)) {
                    $parts = explode(',', $char->$type);
                    
                    if ($type === 'char_skills') {
                        $target_table = 'character_skills';
                        $is_skill = true;
                    } elseif ($type === 'char_talent_skills') {
                        $target_table = 'character_talent_skills';
                        $is_skill = true;
                    } elseif ($type === 'char_senjutsu_skills') {
                        $target_table = 'character_senjutsu_skills';
                        $is_skill = true;
                    } else {
                        $target_table = 'character_items';
                        $is_skill = false;
                    }

                    foreach ($parts as $part) {
                        if (empty($part)) continue;
                        
                        $id = $part;
                        $qty = 1;
                        if (strpos($part, ':') !== false) {
                            list($id, $qty) = explode(':', $part);
                        }

                        $data = [
                            'character_id' => $char->id,
                            'quantity' => (int)$qty,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];

                        if ($is_skill) {
                            $data['skill_id'] = $id;
                        } else {
                            $data['item_type'] = $type;
                            $data['item_id'] = $id;
                        }

                        \Illuminate\Support\Facades\DB::table($target_table)->insert($data);
                    }
                }
            }
        }

        Schema::table('characters', function (Blueprint $table) use ($types) {
            $table->dropColumn($types);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $types = [
            'char_weapons', 'char_pet_weapons', 'char_back_items', 'char_pet_back_items',
            'char_accessories', 'char_sets', 'char_hairs', 'char_skills', 'char_materials',
            'char_essentials', 'char_talent_skills', 'char_senjutsu_skills'
        ];

        Schema::table('characters', function (Blueprint $table) use ($types) {
            foreach ($types as $type) {
                $table->longText($type)->nullable();
            }
        });

        Schema::dropIfExists('character_senjutsu_skills');
        Schema::dropIfExists('character_talent_skills');
        Schema::dropIfExists('character_skills');
        Schema::dropIfExists('character_items');
    }
};
