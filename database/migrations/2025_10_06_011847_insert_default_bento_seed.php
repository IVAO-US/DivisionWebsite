<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert default homepage tours bento seed
        DB::table('app_settings')->insert([
            'key' => 'homepage_tours_bento_seed',
            'value' => '4E9C525BA46A',
            'type' => 'string',
            'description' => 'Seed for the tours bento grid layout on the homepage',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('app_settings')
            ->where('key', 'homepage_tours_bento_seed')
            ->delete();
    }
};