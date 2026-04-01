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
        Schema::create('survey_feedback', function (Blueprint $table) {
            $table->id();
            $table->foreignId('go_session_step_id')
                ->constrained('go_session_steps')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('title');
            $table->longText('description');
            $table->string('status')->default('active')
                ->comment('pending,active,complete');
            $table->decimal('points', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_feedback');
    }
};
