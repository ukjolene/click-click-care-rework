<?php

use Illuminate\Database\Seeder;

class QualificationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('qualifications')->insert([
            'user_id' => '4',
            'position_id' => '1',
        ]);

        DB::table('qualifications')->insert([
            'user_id' => '5',
            'position_id' => '2',
        ]);

        DB::table('qualifications')->insert([
            'user_id' => '6',
            'position_id' => '1',
        ]);
        
    }
}
