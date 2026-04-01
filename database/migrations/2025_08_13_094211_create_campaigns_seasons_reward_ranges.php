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
        Schema::create('campaigns_seasons_reward_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_season_id')->constrained('campaigns_seasons')->onDelete('cascade');
            $table->integer('rank_start');
            $table->integer('rank_end');
            $table->decimal('reward', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns_seasons_reward_ranges');
    }
};
