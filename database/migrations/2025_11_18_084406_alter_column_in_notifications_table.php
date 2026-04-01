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
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['campaign_season_id']);
            $table->dropColumn('campaign_season_id');

            $table->foreignId('department_id')
                ->nullable()
                ->after('company_id')
                ->constrained('company_departments')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn('department_id');

            $table->foreignId('campaign_season_id')
                ->nullable()
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete();
        });
    }
};
