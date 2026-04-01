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
        Schema::create('survey_feedback_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_feedback_attempt_id')
                ->constrained('survey_feedback_attempts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('question_id')
                ->constrained('survey_feedback_questions')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('option_id')
                ->constrained('survey_feedback_question_options')
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
        Schema::dropIfExists('survey_feedback_responses');
    }
};
