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
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->morphs('author'); // author_id and author_type (User or Admin)
            $table->text('content')->comment('Post content/description');
            $table->string('status')->default('pending')->comment('Post status: pending (waiting for admin approval), approved (published), rejected (not approved)');
            $table->timestamp('published_at')->nullable()->comment('Date and time when post was published');
            $table->unsignedBigInteger('approved_by')->nullable()->comment('Admin ID who approved the post');
            $table->text('rejection_reason')->nullable()->comment('Reason for rejection if post was rejected');
            $table->timestamps();
            $table->softDeletes();

            // Indexes (morphs() already creates index for author_type, author_id)
            $table->index('status');
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
