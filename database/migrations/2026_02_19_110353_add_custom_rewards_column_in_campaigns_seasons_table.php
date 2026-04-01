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
        Schema::table('campaigns_seasons', function (Blueprint $table) {
            $table->string('custom_reward')->nullable()->after('reward');
            $table->boolean('custom_reward_status')->default(false)->after('custom_reward');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns_seasons', function (Blueprint $table) {
            $table->dropColumn('custom_reward');
            $table->dropColumn('custom_reward_status');
        });
    }
};
