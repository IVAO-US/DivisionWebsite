<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DefaultAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if default admin already exists
        $existingAdmin = DB::table('admins')->where('vid', 200696)->first();
        
        if (!$existingAdmin) {
            DB::table('admins')->insert([
                'vid' => 200696,
                'permissions' => json_encode(['*']), // Wildcard permission
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
        }
    }
}
