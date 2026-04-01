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
            $table->decimal('reward', 8, 2)->after('end_date')->default(0);
            $table->string('currency')->after('end_date')->default('euro');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns_seasons', function (Blueprint $table) {
            $table->dropColumn('reward');
            $table->dropColumn('currency');
        });
    }
};
