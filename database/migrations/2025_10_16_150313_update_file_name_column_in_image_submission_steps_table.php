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
        Schema::table('image_submission_steps', function (Blueprint $table) {
            $table->string('file_name')->nullable()->comment('Original file name uploaded by user')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_submission_steps', function (Blueprint $table) {
            $table->string('file_name')->comment('Original file name uploaded by user')->change();
        });
    }
};
