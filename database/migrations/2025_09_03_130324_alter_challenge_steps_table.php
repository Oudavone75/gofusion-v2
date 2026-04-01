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
        Schema::table('challenge_steps', function (Blueprint $table) {
            $table->dropForeign('challenge_steps_event_id_foreign');
            $table->dropColumn('event_id');
            $table->string('video_url')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_steps', function (Blueprint $table) {
            //
        });
    }
};
