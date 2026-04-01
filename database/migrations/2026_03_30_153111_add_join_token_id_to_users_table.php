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
        Schema::table('users', function (Blueprint $row) {
            $row->unsignedBigInteger('join_token_id')->nullable()->after('company_id');
            $row->foreign('join_token_id')->references('id')->on('company_join_tokens')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $row) {
            $row->dropForeign(['join_token_id']);
            $row->dropColumn('join_token_id');
        });
    }
};
