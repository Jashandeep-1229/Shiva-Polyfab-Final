<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@shivapolyfab.com',
            'phone' => '1234567890',
            'password' => Hash::make('admin@shivapolyfab.com'),
            'show_password' => 'admin@shivapolyfab.com',
            'role_as' => 'Admin',
            'created_by_id' => '0',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
