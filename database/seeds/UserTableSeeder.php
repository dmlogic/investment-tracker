<?php

use League\Csv\Reader;
use Illuminate\Database\Seeder;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $csv = Reader::createFromPath(__DIR__.'/csv/users.csv');

        $res = $csv->fetchAll();
        foreach($res as $user) {
            factory(App\Models\User::class)->create([
                'email' => $user[0],
                'password' =>  password_hash($user[1],PASSWORD_DEFAULT)
            ]);
        }
    }
}