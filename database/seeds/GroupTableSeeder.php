<?php

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
        factory(\App\Models\Group::class)->create([
            'name' => 'Pension holdings',
            'sort_order' =>  $sort,
        ]);
        factory(\App\Models\Group::class)->create([
            'name' => 'Darren ISA',
            'sort_order' =>  $sort++,
        ]);
        factory(\App\Models\Group::class)->create([
            'name' => 'Ally ISA',
            'sort_order' =>  $sort++,
        ]);
    }
}
