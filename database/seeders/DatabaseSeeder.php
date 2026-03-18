<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolSeeder::class);
        \Artisan::call('permission:cache-reset');

        User::create([
            'name' => 'Cesar Valero Rodriguez',
            'email' => 'admin@admin.com',
            'password' => bcrypt('universal'),
        ])->assignRole('Super');
    }
}
