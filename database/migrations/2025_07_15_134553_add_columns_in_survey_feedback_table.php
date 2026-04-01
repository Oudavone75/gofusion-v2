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
        Schema::table('survey_feedback', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->after('id')
                ->constrained('companies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('campaign_season_id')
                ->nullable()
                ->after('company_id')
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('go_session_id')
                ->nullable()
                ->after('campaign_season_id')
                ->constrained('go_sessions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('created_by')
                ->nullable()
                ->after('go_session_step_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('admin_id')
                ->nullable()
                ->after('created_by')
                ->constrained('admins')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Optional: Add indexes for better performance
            $table->index(['company_id', 'campaign_season_id']);
            $table->index(['campaign_season_id', 'go_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_feedback', function (Blueprint $table) {
            //
        });
    }
};
