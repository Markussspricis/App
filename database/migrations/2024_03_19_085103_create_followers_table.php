<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('follows', function (Blueprint $table) {
            $table->unsignedBigInteger('FollowerID');
            $table->unsignedBigInteger('FollowingID');
            $table->foreign('FollowerID')->references('UserID')->on('users');
            $table->foreign('FollowingID')->references('UserID')->on('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('followers');
    }
};