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
        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('session_time_duration_id')->constrained('session_time_durations')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('language_id')->constrained('languages')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('referral_source')->nullable();
            $table->foreignId('refered_by')
                ->nullable()->constrained('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('rererral_code')->nullable();
            $table->boolean('is_enable_notifications')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
