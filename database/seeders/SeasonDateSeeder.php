<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SeasonDate;
use App\Models\Season;

class SeasonDateSeeder extends Seeder
{
    public function run()
    {
        // Mengambil data musim
        $summer = Season::where('name', 'Summer')->first();
        $winter = Season::where('name', 'Winter')->first();
        $autumn = Season::where('name', 'Autumn')->first();
        $spring = Season::where('name', 'Spring')->first();

        // Menambahkan data musim
        SeasonDate::create([
            'season_id' => $summer->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-02-29'
        ]);

        SeasonDate::create([
            'season_id' => $autumn->id,
            'start_date' => '2024-03-01',
            'end_date' => '2024-05-31'
        ]);

        SeasonDate::create([
            'season_id' => $winter->id,
            'start_date' => '2024-06-01',
            'end_date' => '2024-08-31'
        ]);

        SeasonDate::create([
            'season_id' => $spring->id,
            'start_date' => '2024-09-01',
            'end_date' => '2024-11-30'
        ]);

        SeasonDate::create([
            'season_id' => $summer->id,
            'start_date' => '2024-12-01',
            'end_date' => '2024-12-31'
        ]);


    }
}
