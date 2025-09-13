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
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('vid'); 
            $table->string('custom_email')->nullable();
            $table->string('discord')->nullable();
            $table->boolean('allow_notifications')->default(true);
            $table->timestamps();

            // Index & constraints
            $table->foreign('vid')->references('vid')->on('users')->onDelete('cascade');
            $table->unique('vid'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};