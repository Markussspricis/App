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
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->id('BookmarkID');
            $table->unsignedBigInteger('UserID');
            $table->unsignedBigInteger('TweetID');
            $table->foreign('UserID')->references('UserID')->on('users');
            $table->foreign('TweetID')->references('TweetID')->on('tweets');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
