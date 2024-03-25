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
        Schema::create('users', function (Blueprint $table) {
            $table->id('UserID');
            $table->string('Name');
            $table->string('UserTag')->unique();
            $table->string('Email')->unique();
            $table->date('DOB');
            $table->string('Password');
            $table->string('ProfilePicture')->nullable();
            $table->string('Banner')->nullable();
            $table->string("Description",255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
