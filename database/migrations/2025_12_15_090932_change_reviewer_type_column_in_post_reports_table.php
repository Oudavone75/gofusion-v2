<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('post_reports', function (Blueprint $table) {
            $foreignKeyName = DB::select("
                SELECT CONSTRAINT_NAME
                FROM information_schema.KEY_COLUMN_USAGE
                WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = 'post_reports'
                AND COLUMN_NAME = 'reviewed_by'
                AND CONSTRAINT_NAME LIKE '%foreign%'
            ");
            if (!empty($foreignKeyName)) {
                $table->dropForeign(['reviewed_by']);
            }
            // dd($foreignKeys);
            // $table->dropIndex(['reviewed_by']);
            // $table->dropForeign(['reviewed_by']);
            $table->nullableMorphs('reviewed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('post_reports', function (Blueprint $table) {
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('cascade')->comment('Admin who reviewed the report');
            $table->dropMorphs('reviewed_by');
        });
    }
};
