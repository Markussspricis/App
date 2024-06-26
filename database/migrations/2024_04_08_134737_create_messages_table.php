<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id('MessageID');
            $table->unsignedBigInteger('SenderID');
            $table->unsignedBigInteger('ReceiverID');
            $table->unsignedBigInteger('ConversationID');
            $table->text('Content');
            $table->string('Image')->nullable();
            $table->timestamps();
            $table->foreign('SenderID')->references('UserID')->on('users');
            $table->foreign('ReceiverID')->references('UserID')->on('users');
            $table->foreign('ConversationID')->references('ConversationID')->on('conversations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};