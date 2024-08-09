<?php

namespace Database\Seeders;

use App\Models\MoveableHoliday;
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
        $this->call([
            RetailSeeder::class,
            RatePlanSeeder::class,
            ChargeCategorySeeder::class,
            SeasonSeeder::class,
            SeasonDateSeeder::class,
            FixedHolidaySeeder::class,
            MoveableHolidaySeeder::class,
        ]);
    }
}
