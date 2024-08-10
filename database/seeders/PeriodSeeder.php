<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Retail 1 periods
        DB::table('periods')->insert([
            ['retail_id' => 1, 'name' => 'peek'],
            ['retail_id' => 1, 'name' => 'off peek'],
        ]);

        // Retail 2 periods
        DB::table('periods')->insert([
            ['retail_id' => 2, 'name' => 'peek 1'],
            ['retail_id' => 2, 'name' => 'peek 2'],
            ['retail_id' => 2, 'name' => 'peek 3'],
            ['retail_id' => 2, 'name' => 'off peek'],
            ['retail_id' => 2, 'name' => 'shoulder'],
        ]);
    }
}
