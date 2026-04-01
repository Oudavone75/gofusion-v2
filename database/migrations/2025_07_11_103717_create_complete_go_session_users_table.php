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
        Schema::create('complete_go_session_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaigns_season_id')
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('go_session_id')
                ->constrained('go_sessions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complete_go_session_users');
    }
};
