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
        Schema::table('image_submission_steps', function (Blueprint $table) {
            $table->unsignedInteger('total_points')->default(0)->after('points');
            $table->decimal('percentage', 5, 2)->default(0)->after('total_points');
            $table->json('matched_concepts')->nullable()->after('percentage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('image_submission_steps', function (Blueprint $table) {
            $table->dropColumn(['total_points', 'percentage', 'matched_concepts']);
        });
    }
};
