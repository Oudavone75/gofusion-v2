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
        Schema::table('users', function (Blueprint $table) {
            $table->string('invite_code', 8)->unique()->nullable()->after('email');
            $table->unsignedBigInteger('invited_by')->nullable()->after('invite_code');

            $table->foreign('invited_by')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');

            $table->index('invite_code');
            $table->index('invited_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['invited_by']);
            $table->dropColumn(['invite_code', 'invited_by']);
        });
    }
};
