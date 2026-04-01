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
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_season_id')
                ->constrained('campaigns_seasons')
                ->cascadeOnDelete();
                $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
                $table->foreignId('company_id')
                ->constrained('companies')->nullable()
                ->cascadeOnDelete();
                $table->foreignId('company_department_id')
                ->constrained('company_departments')->nullable()
                ->cascadeOnDelete();
                $table->decimal('amount', 8, 2)->default(0);
                $table->string('status')->default('pending')
                ->comment('pending, approved');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rewards');
    }
};
