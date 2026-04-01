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
        Schema::create('post_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->comment('Foreign key to posts table');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade')->comment('Foreign key to users table');
            $table->text('comment')->comment('Comment text content');
            $table->foreignId('parent_comment_id')->nullable()->constrained('post_comments')->onDelete('cascade')->comment('For 2-level comments: null for parent comments (level 1), set for replies (level 2)');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('post_id');
            $table->index('user_id');
            $table->index('parent_comment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_comments');
    }
};
