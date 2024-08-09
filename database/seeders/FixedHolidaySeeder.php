<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FixedHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('fixed_holidays')->insert([
            // Tahun 2024
            ['name' => 'New Year\'s Day', 'month' => 1, 'day' => 1],    // 1 Januari
            ['name' => 'Australia Day', 'month' => 1, 'day' => 26],     // 26 Januari
            ['name' => 'Good Friday', 'month' => 3, 'day' => 29],       // 29 Maret
            ['name' => 'Easter Monday', 'month' => 4, 'day' => 1],       // 1 April
            ['name' => 'Anzac Day', 'month' => 4, 'day' => 25],          // 25 April
            ['name' => 'Queen\'s Birthday', 'month' => 6, 'day' => 10],  // 10 Juni
            ['name' => 'Labour Day', 'month' => 10, 'day' => 7],         // 7 Oktober
            ['name' => 'Christmas Day', 'month' => 12, 'day' => 25],     // 25 Desember
            ['name' => 'Boxing Day', 'month' => 12, 'day' => 26],        // 26 Desember
        ]);

    }
}
