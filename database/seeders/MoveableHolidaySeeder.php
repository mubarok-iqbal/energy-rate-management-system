<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MoveableHolidaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('moveable_holidays')->insert([
            ['name' => 'Easter Monday', 'date' => '2024-04-01'],
            ['name' => 'Labour Day', 'date' => '2024-10-07'],
            ['name' => 'January Festival', 'date' => '2024-01-02'],
        ]);
    }
}
