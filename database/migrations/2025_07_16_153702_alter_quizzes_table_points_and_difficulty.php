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
        Schema::table('quizzes', function (Blueprint $table) {
            // Change points from decimal to integer
            $table->integer('points')->default(0)->change();

            // Make difficulty nullable
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->nullable()->change();
            
            // Convert theme string to foreign key
            $table->renameColumn('theme', 'theme_id');
            $table->unsignedBigInteger('theme_id')->nullable()->change();
            
            // Add foreign key constraint
            $table->foreign('theme_id')
                  ->references('id')
                  ->on('themes')
                  ->cascadeOnDelete()
                  ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            // Remove foreign key first
            $table->dropForeign(['theme_id']);
            
            // Revert to string column
            $table->renameColumn('theme_id', 'theme');
            $table->string('theme')->nullable()->change();
            
            // Revert points back to decimal
            $table->decimal('points', 8, 2)->default(0)->change();

            // Revert difficulty back to not nullable with default
            $table->enum('difficulty', ['easy', 'medium', 'hard'])->default('medium')->change();
        });
    }
};