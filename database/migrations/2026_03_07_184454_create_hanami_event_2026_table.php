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
        Schema::create('hanami_event_2026', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('char_id')->unique();
            $table->integer('energy')->default(100);
            $table->integer('max_energy')->default(100);
            $table->timestamp('last_energy_refill')->nullable();
            $table->boolean('can_claim_free_gift')->default(true);
            $table->integer('total_draws')->default(0);
            $table->text('battle_kills')->nullable(); // JSON: [6 bosses]
            $table->text('battle_claims')->nullable(); // JSON: [[],[],[],[],[],[]]
            $table->text('gacha_claims')->nullable(); // JSON
            $table->text('tasks_status')->nullable(); // JSON
            $table->integer('pending_boss_idx')->nullable();
            $table->integer('pack_0')->default(0); // Training
            $table->integer('pack_1')->default(0); 
            $table->integer('pack_2')->default(0); 
            $table->integer('deal_0')->default(0); // Deals
            $table->integer('deal_1')->default(0);
            $table->integer('deal_2')->default(0);
            $table->integer('deal_3')->default(0);
            $table->integer('deal_4')->default(0);
            $table->integer('deal_5')->default(0);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hanami_event_2026');
    }
};
