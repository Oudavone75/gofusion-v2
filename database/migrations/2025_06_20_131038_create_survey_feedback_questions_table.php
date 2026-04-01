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
        Schema::create('survey_feedback_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survay_feedback_id')->constrained('survey_feedback')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->text('question_text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_feedback_questions');
    }
};
