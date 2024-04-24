<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['ConversationID']);
            $table->foreign('ConversationID')
                  ->references('ConversationID')->on('conversations')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['ConversationID']);
            $table->foreign('ConversationID')
                  ->references('ConversationID')->on('conversations');
        });
    }
};
