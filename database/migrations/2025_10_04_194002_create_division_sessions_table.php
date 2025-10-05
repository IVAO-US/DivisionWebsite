<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SessionType;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('division_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->time('time_start');
            $table->time('time_end');
            $table->enum('type', SessionType::values());
            $table->json('training_details')->nullable();
            $table->string('illustration')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('last_log_id')->unique();
            $table->timestamps();

            // Indexes for better query performance
            $table->index('date');
            $table->index('type');
            $table->index('last_log_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('division_sessions');
    }
};