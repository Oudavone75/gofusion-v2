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
        Schema::table('user_carbon_footprints', function (Blueprint $table) {
            $table->string('carbon_unit')->after('attempt_at')->nullable();
            $table->string('carbon_value')->after('attempt_at')->nullable();
            $table->string('water_unit')->after('attempt_at')->nullable();
            $table->string('water_value')->after('attempt_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_carbon_footprints', function (Blueprint $table) {
            $table->dropColumn('carbon_unit');
            $table->dropColumn('carbon_value');
            $table->dropColumn('water_unit');
            $table->dropColumn('water_value');
        });
    }
};
