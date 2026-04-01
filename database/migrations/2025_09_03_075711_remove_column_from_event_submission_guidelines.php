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
        Schema::table('event_submission_guidelines', function (Blueprint $table) {
            $table->dropForeign('event_submission_guidelines_event_id_foreign');
            $table->dropColumn('event_id');
            $table->dropColumn('event_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('event_submission_guidelines', function (Blueprint $table) {
            //
        });
    }
};
