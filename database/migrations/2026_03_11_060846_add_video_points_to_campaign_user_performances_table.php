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
        Schema::table('campaign_user_performances', function (Blueprint $table) {
            $table->decimal('video_total_earned_points', 8, 2)->default(0)->after('video_score_percentage');
            $table->decimal('video_total_possible_points', 8, 2)->default(0)->after('video_total_earned_points');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_user_performances', function (Blueprint $table) {
            $table->dropColumn('video_total_earned_points');
            $table->dropColumn('video_total_possible_points');
        });
    }
};
