<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\Widgets\MultiSeriesTrendSeeder;
use Database\Seeders\Widgets\PieChartSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            MultiSeriesTrendSeeder::class,
            PieChartSeeder::class,
            UserSeeder::class,
        ]);


    }
}
