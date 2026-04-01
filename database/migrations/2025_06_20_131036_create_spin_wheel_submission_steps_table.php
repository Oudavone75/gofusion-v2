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
        Schema::create('spin_wheel_submission_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('spin_wheel_id')
                ->constrained('spin_wheels')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('go_session_step_id')
                ->constrained('go_session_steps')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('bonus_type')
                ->comment('video_urls, bonus_leaves, promo_codes');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spin_wheel_submission_steps');
    }
};
