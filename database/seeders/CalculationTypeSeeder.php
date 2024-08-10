<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CalculationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('calculation_types')->insert([
            ['name' => 'fix_per_day'],
            ['name' => 'fix_per_month'],
            ['name' => 'period'],
        ]);
    }
}
