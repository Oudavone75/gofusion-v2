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
        Schema::create('image_submission_guidelines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('go_session_step_id')
                ->constrained('go_session_steps')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('guideline_type')->default('text')
                ->comment('text, file');
            $table->string('guideline_file')->nullable();
            $table->longText('guideline_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('image_submission_guidelines');
    }
};
