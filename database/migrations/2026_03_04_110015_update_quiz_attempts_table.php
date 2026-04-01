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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->decimal('total_points', 8, 2)->default(0)->after('points');
            $table->decimal('percentage', 5, 2)->default(0)->after('total_points');
            $table->integer('attempt_number')->default(1)->after('percentage');
            $table->foreignId('campaign_season_id')->nullable()->constrained('campaigns_seasons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'percentage', 'attempt_number']);
            $table->dropForeign(['campaign_season_id']);
            $table->dropColumn('campaign_season_id');
        });
    }
};
