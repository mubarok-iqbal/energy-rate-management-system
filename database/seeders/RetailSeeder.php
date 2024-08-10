<?php

namespace Database\Seeders;

use App\Models\Retail;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RetailSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Retail::create([
            'name' => 'Energy Corp',
            'contact_info' => 'contact@energycorp.com',
            'address' => '123 Energy Street, Jakarta'
        ]);

        Retail::create([
            'name' => 'Power Solutions',
            'contact_info' => 'info@powersolutions.com',
            'address' => '456 Power Avenue, Bandung',
            'is_active' => false
        ]);
    }
}
