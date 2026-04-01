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
        Schema::create('go_user_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaigns_season_id')
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('go_session_id')
                ->constrained('go_sessions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('go_session_step_id')
                ->constrained('go_session_steps')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->boolean('is_complete')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('go_user_progress');
    }
};
