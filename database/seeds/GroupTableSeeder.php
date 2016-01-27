<?php

use League\Csv\Reader;
use Illuminate\Database\Seeder;

class GroupTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sort = 1;
        $csv = Reader::createFromPath(__DIR__.'/csv/groups.csv');

        $res = $csv->setOffset(1)->fetchAll();
        foreach($res as $group) {
            factory(\App\Models\Group::class)->create([
                'name' => $group[0],
                'sort_order' =>  $group[1],
            ]);
        }
    }
}
