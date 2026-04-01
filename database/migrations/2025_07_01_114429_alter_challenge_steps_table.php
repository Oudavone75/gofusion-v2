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
        Schema::table('challenge_steps', function (Blueprint $table) {
            $table->dropForeign(['go_session_step_id']);
        });

        Schema::table('challenge_steps', function (Blueprint $table) {
            $table->foreignId('campaign_id')
                ->nullable()
                ->after('user_id')
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete();

            $table->foreignId('theme_id')
                ->nullable()
                ->after('campaign_id')
                ->constrained('themes')
                ->cascadeOnDelete();

            $table->foreignId('company_id')
                ->nullable()
                ->after('theme_id')
                ->constrained('companies')
                ->cascadeOnDelete();

            $table->foreignId('department_id')
                ->nullable()
                ->after('company_id')
                ->constrained('company_departments')
                ->cascadeOnDelete();

            $table->unsignedBigInteger('go_session_step_id')->nullable()->after('challenge_category_id')->change();

            $table->foreign('go_session_step_id')
                ->references('id')
                ->on('go_session_steps')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->unsignedInteger('points')->after('status')->default(0);
            $table->boolean('is_global')->nullable()->after('points')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
