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
        Schema::create('comment_likes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comment_id')->constrained('post_comments')->onDelete('cascade')->comment('Foreign key to post_comments table');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('User who liked the comment');
            $table->timestamps();

            // Indexes for performance
            $table->index('comment_id');
            $table->index('user_id');

            // Prevent duplicate likes from same user on same comment
            $table->unique(['comment_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comment_likes');
    }
};
