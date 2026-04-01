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
        Schema::create('user_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrainted('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->string('transaction_type')->default('credited')->comment('credited or debited');
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('EURO');
            $table->string('transaction_id')->unique()->nullable();
            $table->string('transaction_status')->default('pending')->comment('pending or success');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_transactions');
    }
};
