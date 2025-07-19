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
        /* IVAO Users */
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->integer('vid')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->integer('rating_atc');
            $table->integer('rating_pilot');
            $table->string('gca')->nullable();
            $table->integer('hours_atc')->nullable();
            $table->integer('hours_pilot')->nullable();
            $table->string('country');
            $table->string('division');
            $table->text('staff')->nullable(); 
            $table->rememberToken();
            $table->timestamps();
        });


        /* Laravel Sessions */
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });


        /* For compatibility purposes: NOT USED */
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
    }
};
?>