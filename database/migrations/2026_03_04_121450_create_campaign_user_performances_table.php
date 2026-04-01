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
        Schema::create('campaign_user_performances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('campaign_season_id')->constrained('campaigns_seasons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('quiz_total_earned_points', 10, 2)->default(0);
            $table->decimal('quiz_total_possible_points', 10, 2)->default(0);
            $table->decimal('quiz_score_percentage', 5, 2)->default(0);
            $table->decimal('video_score_percentage', 5, 2)->default(0); // For future
            $table->decimal('global_score_percentage', 5, 2)->default(0);
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_user_performances');
    }
};
