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
        Schema::create('valentine_event_2026', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('char_id')->unique();
            $table->integer('energy')->default(100);
            $table->integer('max_energy')->default(100);
            $table->timestamp('last_energy_refill')->nullable();
            $table->boolean('can_claim_free_gift')->default(true);
            $table->integer('total_draws')->default(0);
            $table->text('battle_kills')->nullable(); // JSON: [boss0kills, boss1kills, boss2kills]
            $table->text('battle_claims')->nullable(); // JSON: [[claimedRewardsIdxs],[],[]]
            $table->text('gacha_claims')->nullable(); // JSON: [claimedRewardIdxs]
            $table->text('tasks_status')->nullable(); // JSON: [{task_id, current, claimed}, ...]
            $table->integer('pack_0')->default(0); // Training pack 0
            $table->integer('pack_1')->default(0); // Training pack 1
            $table->integer('pack_2')->default(0); // Training pack 2
            $table->integer('deal_0')->default(0); // Deals pack 0
            $table->integer('deal_1')->default(0); // Deals pack 1
            $table->integer('deal_2')->default(0); // Deals pack 2
            $table->integer('deal_3')->default(0); // Deals pack 3
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valentine_event_2026');
    }
};
