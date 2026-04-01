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
        Schema::create('post_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade')->comment('Foreign key to posts table');
            $table->string('media_type')->comment('Type of media: image, video, pdf, link');
            $table->string('file_path')->nullable()->comment('Path to the uploaded file (for image, video, pdf)');
            $table->string('link_url')->nullable()->comment('URL for link type media');
            $table->string('thumbnail_path')->nullable()->comment('Thumbnail path for videos or link previews');
            $table->integer('file_size')->nullable()->comment('File size in KB');
            $table->string('mime_type')->nullable()->comment('MIME type of the uploaded file');
            $table->integer('order')->default(0)->comment('Order of media items in the post (for multiple media)');
            $table->timestamps();

            // Indexes
            $table->index('post_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_media');
    }
};
