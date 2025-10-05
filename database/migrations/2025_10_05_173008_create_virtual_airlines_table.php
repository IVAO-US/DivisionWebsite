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
        Schema::create('virtual_airlines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('icao_code', 3);
            $table->json('hubs'); // Array of ICAO codes
            $table->text('description');
            $table->string('banner');
            $table->string('link');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('virtual_airlines');
    }
};
