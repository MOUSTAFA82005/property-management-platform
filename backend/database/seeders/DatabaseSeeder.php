<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'owner@propspace.com'],
            [
                'name'     => 'Property Owner',
                'password' => 'password',
                'role'     => 'owner',
                'status'   => 'active',
            ]
        );

        User::updateOrCreate(
            ['email' => 'customer@propspace.com'],
            [
                'name'     => 'Customer User',
                'password' => 'password',
                'role'     => 'customer',
                'status'   => 'active',
            ]
        );
    }
}
