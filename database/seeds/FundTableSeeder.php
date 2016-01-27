<?php

use League\Csv\Reader;
use Illuminate\Database\Seeder;

class FundTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $csv = Reader::createFromPath(__DIR__.'/funds.csv');

        $res = $csv->setOffset(1)->fetchAll();
        foreach($res as $fund) {
            factory(App\Models\Fund::class)->create([
                'group_id'    => $fund[0],
                'name'        => $fund[1],
                'type'        => $fund[2],
                'cost_price'  => $fund[3],
                'units_held'  => $fund[4],
                'broker'      => $fund[6],
                'currency'    => $fund[7],
                'price_units' => $fund[8],
                'url'         => $fund[9],
                'chart_code'  => $fund[10],
            ]);
        }
    }
}
