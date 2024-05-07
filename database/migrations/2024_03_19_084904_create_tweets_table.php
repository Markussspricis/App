<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tweets', function (Blueprint $table) {
            $table->id('TweetID');
            $table->string('TweetText')->nullable();;
            $table->string('TweetImage')->nullable();
            $table->unsignedBigInteger('UserID');
            $table->foreign('UserID')->references('UserID')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tweets');
    }
};