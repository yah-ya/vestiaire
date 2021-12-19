<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('currencies')->insert([
            [
                'id'=>1,
                'title' => 'USD',
            ],
            [
                'id'=>2,
                'title' => 'EUR',
            ],
            [
                'id'=>3,
                'title' => 'GBP',
            ]
        ]);

        DB::table('sellers')->insert([
           [
               'name'=>'Test Seller'
           ]
        ]);
    }
}
