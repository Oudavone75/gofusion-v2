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
        Schema::table('go_user_progress', function (Blueprint $table) {
            $table->foreignId('user_id')->after('go_session_step_id')->constrained('users')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('go_user_progress', function (Blueprint $table) {
            $table->dropColumn('user_id');
        });
    }
};
