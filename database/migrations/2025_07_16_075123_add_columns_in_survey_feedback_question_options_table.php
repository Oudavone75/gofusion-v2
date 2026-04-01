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
        Schema::table('survey_feedback_question_options', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('question_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('admin_id')
                ->nullable()
                ->after('created_by')
                ->constrained('admins')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_feedback_question_options', function (Blueprint $table) {
            //
        });
    }
};
