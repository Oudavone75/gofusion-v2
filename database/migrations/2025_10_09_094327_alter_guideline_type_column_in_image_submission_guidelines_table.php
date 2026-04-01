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
        Schema::table('image_submission_guidelines', function (Blueprint $table) {
            $table->renameColumn('guideline_type', 'mode');

            $table->string('mode')->default('photo')->comment('photo, video, checkbox')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_submission_guidelines', function (Blueprint $table) {
            $table->renameColumn('mode', 'guideline_type')->default('text')
                ->comment('text, file');
        });
    }
};
