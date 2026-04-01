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
        Schema::create('survey_feedback_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('go_session_step_id')
                ->constrained('go_session_steps')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('survey_feedback_id')->constrained('survey_feedback')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->decimal('points', 8, 2)->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_feedback_attempts');
    }
};
