<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\TimeType;
use Illuminate\Database\Seeder;
use App\Models\ChargeSubCategory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChargeSubCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fixPerDay = TimeType::where('name', 'fix_per_day')->first();
        $period = TimeType::where('name', 'period')->first();
        $fixPerMonth = TimeType::where('name', 'fix_per_month')->first();

        $winter = Season::where('name', 'winter')->first();
        $summer = Season::where('name', 'summer')->first();

        ChargeSubCategory::create([
            'charge_category_id' => 1,
            'name' => 'Daily Charge',
            'time_type_id' => $fixPerDay->id,
            'season_id' => null,
            'days' => null,
            'start_time' => null,
            'end_time' => null,
            'loss_factor' => 0.02,
            'unit_price' => 1.506849,
            'threshold' => null,
        ]);


        ChargeSubCategory::create([
            'charge_category_id' => 1,
            'name' => 'Off Peak',
            'time_type_id' => $period->id,
            'season_id' => null,
            'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            'start_time' => '14:01',
            'end_time' => '20:00',
            'loss_factor' => 1.0450,
            'unit_price' => 0.063960,
            'threshold' => null,
        ]);

        ChargeSubCategory::create([
            'charge_category_id' => 1,
            'name' => 'Off Peak',
            'time_type_id' => $period->id,
            'season_id' => null,
            'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
            'start_time' => '20:01',
            'end_time' => '14:00',
            'loss_factor' => 1.0450,
            'unit_price' => 0.077990,
            'threshold' => null,
        ]);

    }
}
