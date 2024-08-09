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

        // Menambahkan data musim
        SeasonDate::create([
            'season_id' => $summer->id,
            'start_date' => '2024-12-01',
            'end_date' => '2024-02-29' // 29 Februari 2024, tahun kabisat
        ]);

        SeasonDate::create([
            'season_id' => $winter->id,
            'start_date' => '2024-06-01',
            'end_date' => '2024-08-31'
        ]);
    }
}
