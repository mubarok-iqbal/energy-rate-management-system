<?php

namespace Database\Seeders;

use App\Models\Season;
use App\Models\UnitPrice;
use App\Models\CalculationType;
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
        $fixPerDay = CalculationType::where('name', 'fix_per_day')->first();
        $period = CalculationType::where('name', 'period')->first();
        $fixPerMonth = CalculationType::where('name', 'fix_per_month')->first();

        $winter = Season::where('name', 'winter')->first();
        $summer = Season::where('name', 'summer')->first();

        //Enegergy Charge - Daily Charge
        $dailyCharge = ChargeSubCategory::create([
            'charge_category_id' => 1,
            'name' => 'Daily Charge',
            'calculation_type_id' => $fixPerDay->id,
            'season_id' => null,
            'period_id' => null,
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $dailyCharge->id,
            'price' => 1.506849
        ]);

        $offPeak = ChargeSubCategory::create([
            'charge_category_id' => 1,
            'name' => 'Off Peak',
            'calculation_type_id' => $period->id,
            'season_id' => null,
            'period_id' => 2,
            'loss_factor' => 1.0450
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $offPeak->id,
            'price' => 0.063960,
        ]);

        $peek = ChargeSubCategory::create([
            'charge_category_id' => 1,
            'name' => 'Peak',
            'calculation_type_id' => $period->id,
            'season_id' => null,
            'period_id' => 1,
            'loss_factor' => 1.0450
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $peek->id,
            'price' => 0.077990,
        ]);

        //Metering Charge
        $meteingCharge = ChargeSubCategory::create([
            'charge_category_id' => 2,
            'name' => 'Metering Charge',
            'calculation_type_id' => $fixPerDay->id,
            'season_id' => null,
            'period_id' => null,
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $meteingCharge->id,
            'price' => 4.794521
        ]);

        //Network Charge
        $netwrokCharge = ChargeSubCategory::create([
            'charge_category_id' => 3,
            'name' => 'Network Charge',
            'calculation_type_id' => $fixPerDay->id,
            'season_id' => null,
            'period_id' => null,
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $netwrokCharge->id,
            'price' => 3.288
        ]);

        //summer demand dan witner demand
        $summerDemand = ChargeSubCategory::create([
            'charge_category_id' => 4,
            'name' => 'Summer Demand (KW/Mth)',
            'calculation_type_id' => $fixPerMonth->id,
            'season_id' => 1,
            'period_id' => null,
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $summerDemand->id,
            'price' => 15.7,
        ]);

        $winterDemand = ChargeSubCategory::create([
            'charge_category_id' => 4,
            'name' => 'Winter Demand (KW/Mth)',
            'calculation_type_id' => $fixPerMonth->id,
            'season_id' => 2,
            'period_id' => null,
        ]);

        UnitPrice::create([
            'charge_sub_category_id' => $winterDemand->id,
            'price' => 17,
        ]);
    }
}
