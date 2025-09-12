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
        Schema::create('gdpr_deletion_logs', function (Blueprint $table) {
            $table->id();
            $table->integer('user_vid'); 
            $table->string('user_full_name'); 
            $table->string('user_email'); 
            $table->integer('admin_vid'); 
            $table->string('admin_name'); 
            $table->string('control_key', 64); 
            $table->json('deleted_data'); 
            $table->text('reason')->nullable(); 
            $table->timestamp('executed_at'); 
            $table->timestamps();
            
            // Indexes
            $table->index('user_vid');
            $table->index('admin_vid');
            $table->index('executed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gdpr_deletion_logs');
    }
};