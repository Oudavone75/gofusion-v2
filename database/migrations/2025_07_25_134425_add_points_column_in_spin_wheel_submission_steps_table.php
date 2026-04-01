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
        Schema::table('spin_wheel_submission_steps', function (Blueprint $table) {
            $table->decimal('points', 8, 2)->after('bonus_type')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('spin_wheel_submission_steps', function (Blueprint $table) {
            $table->dropColumn('points');
        });
    }
};
