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
        Schema::table('challenge_steps', function (Blueprint $table) {
            $table->string('status')->default('pending')->comment('pending,rejected,approved')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_steps', function (Blueprint $table) {
            $table->string('status')->default('active')->comment('pending,rejected,approved')->change();
        });
    }
};
