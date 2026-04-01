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
        Schema::create('challenge_departments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('challenge_step_id');
            $table->unsignedBigInteger('company_department_id');
            $table->timestamps();

            $table->foreign('challenge_step_id')->references('id')->on('challenge_steps')->onDelete('cascade');
            $table->foreign('company_department_id')->references('id')->on('company_departments')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_departments');
    }
};
