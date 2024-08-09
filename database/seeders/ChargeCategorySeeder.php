<?php

namespace Database\Seeders;

use App\Models\RatePlan;
use App\Models\ChargeCategory;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ChargeCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get rate plan IDs for Retail 1 (Energy Corp)
        $ratePlansRetail1 = RatePlan::where('retail_id', 1)->get();

        $chargeCategoriesRetail1 = [
            [
                'name' => 'Energy Charges',
                'description' => 'Charges related to energy consumption.',
            ],
            [
                'name' => 'Metering Charges',
                'description' => 'Charges related to metering services.',
            ],
            [
                'name' => 'Network Charges',
                'description' => 'Charges for using the energy network.',
            ],
            [
                'name' => 'Demand Charges',
                'description' => 'Charges based on demand.',
            ],
        ];

        foreach ($ratePlansRetail1 as $ratePlan) {
            foreach ($chargeCategoriesRetail1 as $category) {
                ChargeCategory::create([
                    'rate_plan_id' => $ratePlan->id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]);
            }
        }

        // Get rate plan IDs for Retail 2 (Power Solutions)
        $ratePlansRetail2 = RatePlan::where('retail_id', 2)->get();

        $chargeCategoriesRetail2 = [
            [
                'name' => 'Usage Charges',
                'description' => 'Charges based on energy usage.',
            ],
            [
                'name' => 'Network Usage Charges',
                'description' => 'Charges for using the network infrastructure.',
            ],
            [
                'name' => 'Other Charges and Credits',
                'description' => 'Miscellaneous charges and credits.',
            ],
        ];

        foreach ($ratePlansRetail2 as $ratePlan) {
            foreach ($chargeCategoriesRetail2 as $category) {
                ChargeCategory::create([
                    'rate_plan_id' => $ratePlan->id,
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ]);
            }
        }
    }
}
