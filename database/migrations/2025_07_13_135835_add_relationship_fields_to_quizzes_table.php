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
        Schema::table('quizzes', function (Blueprint $table) {
            $table->foreignId('company_id')
                ->nullable()
                ->constrained('companies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->after('id');
            $table->foreignId('campaign_season_id')
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->after('company_id');
            $table->foreignId('go_session_id')
                ->constrained('go_sessions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate()
                ->after('campaign_season_id');
            $table->string('quiz_type')->after('go_session_id');
            $table->string('theme')->nullable()->after('go_session_id');
            $table->string('language', 10)->default('FR')->after('theme');
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->after('language');
            $table->unsignedTinyInteger('num_questions')->nullable()->after('difficulty');
            $table->unsignedTinyInteger('num_options')->nullable()->after('num_questions');
            $table->text('ai_rules')->nullable()->after('num_options');
            // Add indexes for better query performance
            $table->index(['company_id', 'campaign_season_id']);
            $table->index(['campaign_season_id', 'go_session_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['campaign_season_id']);
            $table->dropForeign(['go_session_id']);

            $table->dropIndex(['company_id', 'campaign_season_id']);
            $table->dropIndex(['campaign_season_id', 'go_session_id']);

            $table->dropColumn(['company_id', 'campaign_season_id', 'go_session_id']);
        });
    }
};
