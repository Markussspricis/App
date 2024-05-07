<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retweets', function (Blueprint $table) {
            $table->id('RetweetID');
            $table->unsignedBigInteger('TweetID');
            $table->unsignedBigInteger('UserID');
            $table->foreign('TweetID')->references('TweetID')->on('tweets');
            $table->foreign('UserID')->references('UserID')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retweets');
    }
};