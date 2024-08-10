<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PeriodDetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $retail_1_peek = DB::table('periods')->where('retail_id', 1)->where('name' , 'peek')->first();
        $retail_1_off_peek = DB::table('periods')->where('retail_id', 1)->where('name' , 'off peek')->first();

        $retail_2_peek = DB::table('periods')->where('retail_id', 2)->where('name' , 'peek')->first();
        $retail_2_off_peek = DB::table('periods')->where('retail_id', 2)->where('name' , 'off peek')->first();
        $retail_2_off_peek = DB::table('periods')->where('retail_id', 2)->where('name' , 'shoulder')->first();

        //Retail 1 peek
        DB::table('period_details')->insert([
            [
                'period_id' => $retail_1_peek,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'start_time' => '14:00:01',
                'end_time' => '20:00:00',
            ],
        ]);

        //Retail 1 off peek
        DB::table('period_details')->insert([
            [
                'period_id' => $retail_1_off_peek,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'start_time' => '20:00:01',
                'end_time' => '14:00:00',
            ],
        ]);

        DB::table('period_details')->insert([
            [
                'period_id' => $retail_1_off_peek,
                'days' => json_encode(['Saturday', 'Sunday']),
                'start_time' => '00:00:01',
                'end_time' => '24:00:00',
            ],
        ]);

        //Retail 2 peek
        DB::table('period_details')->insert([
            [
                'period_id' => $retail_2_peek,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'start_time' => '13:00:01',
                'end_time' => '18:00:00',
            ],
        ]);

        //Retail 2 shoulder
        DB::table('period_details')->insert([
            [
                'period_id' => $retail_2_shoulder,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'start_time' => '18:00:01',
                'end_time' => '20:00:00',
            ],
        ]);

        //Retail 2 off peek
        DB::table('period_details')->insert([
            [
                'period_id' => $retail_2_off_peek,
                'days' => json_encode(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']),
                'start_time' => '20:00:01',
                'end_time' => '13:00:00',
            ],
        ]);

        DB::table('period_details')->insert([
            [
                'period_id' => $retail_2_off_peek,
                'days' => json_encode(['Saturday', 'Sunday']),
                'start_time' => '00:00:01',
                'end_time' => '24:00:00',
            ],
        ]);
    }
}
