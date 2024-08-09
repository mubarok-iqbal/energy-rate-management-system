<?php

namespace Database\Seeders;

use App\Models\Season;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class SeasonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Season::create(['name' => 'Summer']);
        Season::create(['name' => 'Winter']);
    }
}
