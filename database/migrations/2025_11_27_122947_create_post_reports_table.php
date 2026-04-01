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
        Schema::create('post_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->comment('Foreign key to posts table');
            $table->foreignId('reported_by')->constrained('users')->onDelete('cascade')->comment('User who reported the post');
            $table->string('reason')->comment('Reason for reporting: spam, inappropriate, harassment, violence, misinformation, other');
            $table->text('description')->nullable()->comment('Additional description/details about the report');
            $table->string('status')->default('pending')->comment('Status: pending, reviewed, resolved, dismissed');
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->onDelete('set null')->comment('Admin who reviewed the report');
            $table->timestamp('reviewed_at')->nullable()->comment('When the report was reviewed');
            $table->timestamps();

            // Indexes for performance
            $table->index('post_id');
            $table->index('reported_by');
            $table->index('status');

            // Prevent duplicate reports from same user for same post
            $table->unique(['post_id', 'reported_by']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_reports');
    }
};
