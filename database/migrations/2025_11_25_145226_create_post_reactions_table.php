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
        Schema::create('post_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->comment('Foreign key to posts table');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Foreign key to users table');
            $table->string('reaction_type')->default('❤️')->comment('Type of reaction: ❤️, 👍, 🤗, 😂, 😮, 😢, 😡');
            $table->timestamps();

            // Unique constraint: one user can only have one reaction per post
            $table->unique(['post_id', 'user_id']);

            // Indexes
            $table->index('post_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reactions');
    }
};
