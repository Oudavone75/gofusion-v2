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
        Schema::table('user_companies', function (Blueprint $table) {
            $table->foreignId('company_department_id')
                ->after('company_id')
                ->nullable()
                ->constrained('company_departments')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_companies', function (Blueprint $table) {
            //
        });
    }
};
