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
        Schema::create('event_themes', function (Blueprint $table) {
            $table->foreignId('event_id')->constrained('events')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('theme_id')->constrained('themes')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->primary(['event_id', 'theme_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_themes');
    }
};
