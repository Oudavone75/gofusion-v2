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
            // Make created_by nullable
            $table->foreignId('created_by')->nullable()->change();

            // Add nullable admin_id with foreign key constraints
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
        Schema::table('quizzes', function (Blueprint $table) {
            // Drop admin_id foreign key and column
            $table->dropForeign(['admin_id']);
            $table->dropColumn('admin_id');

            // Make created_by not nullable again (reverting change)
            $table->foreignId('created_by')->nullable(false)->change();
        });
    }
};
