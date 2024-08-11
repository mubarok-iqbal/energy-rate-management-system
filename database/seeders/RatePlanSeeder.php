<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RatePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Mendapatkan ID retail yang ada
        $retails = DB::table('retails')->get();

        // Contoh data rate plans untuk masing-masing retail
        foreach ($retails as $retail) {
            DB::table('rate_plans')->insert([
                [
                    'retail_id' => $retail->id,
                    'name' => 'Standard Plan',
                    'is_active' => true,
                    'description' => $retail->name . 'Standard Plan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'retail_id' => $retail->id,
                    'name' => 'Premium Plan',
                    'is_active' => false,
                    'description' => $retail->name . 'Premium Plan',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
        }
    }
}

