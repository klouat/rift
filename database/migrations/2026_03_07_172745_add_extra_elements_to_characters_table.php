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
            $table->integer('element_2')->default(0)->after('element');
            $table->integer('element_3')->default(0)->after('element_2');
            $table->integer('element_4')->default(0)->after('element_3');
            $table->integer('element_5')->default(0)->after('element_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->dropColumn(['element_2', 'element_3', 'element_4', 'element_5']);
        });
    }
};
