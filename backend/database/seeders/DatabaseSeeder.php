<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Everything lives in DemoDataSeeder because the demo is one connected
     * scenario — owners, their portfolios, the customers renting from them
     * and the money moving between them all reference each other, so
     * splitting it across independent seeders would only hide the ordering.
     */
    public function run(): void
    {
        $this->call([
            DemoDataSeeder::class,
        ]);
    }
}
