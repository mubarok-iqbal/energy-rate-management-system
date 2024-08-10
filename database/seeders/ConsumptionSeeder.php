<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConsumptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customers = DB::table('customers')->pluck('id'); // Ambil semua customer_id
        $start = Carbon::create(2024, 7, 1, 0, 0, 0); // Awal Juli 2024
        $end = Carbon::create(2024, 8, 1, 0, 0, 0); // Awal Agustus 2024

        foreach ($customers as $customer_id) {
            $currentStart = $start->copy();

            while ($currentStart->lessThan($end)) {
                $currentEnd = $currentStart->copy()->addMinutes(30);
                $now = Carbon::now();

                DB::table('consumptions')->insert([
                    'customer_id' => $customer_id,
                    'start_time' => $currentStart,
                    'end_time' => $currentEnd,
                    'usage' => rand(100000, 170000) / 10000,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);

                $currentStart = $currentEnd;
            }
        }
    }
}
