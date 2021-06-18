<?php

use Illuminate\Database\Seeder;

class ValuesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //Provider 1
        DB::table('values')->insert([
            'user_id' => '4',
            'property_id' => '1',
            'value' => str_random(10),
        ]);
        
        DB::table('values')->insert([
            'user_id' => '4',
            'property_id' => '2',
            'value' => 'Dr.',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '4',
            'property_id' => '3',
            'value' => 'English',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '4',
            'property_id' => '4',
            'value' => '50',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '4',
            'property_id' => '5',
            'value' => str_random(50),
        ]);
        
        DB::table('values')->insert([
            'user_id' => '4',
            'property_id' => '6',
            'value' => '1',
        ]);

        //Provider 2
        DB::table('values')->insert([
            'user_id' => '5',
            'property_id' => '1',
            'value' => str_random(10),
        ]);
        
        DB::table('values')->insert([
            'user_id' => '5',
            'property_id' => '2',
            'value' => 'Dr.',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '5',
            'property_id' => '3',
            'value' => 'English;Chinese',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '5',
            'property_id' => '4',
            'value' => '50',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '5',
            'property_id' => '5',
            'value' => str_random(50),
        ]);
        
        DB::table('values')->insert([
            'user_id' => '5',
            'property_id' => '6',
            'value' => '1',
        ]);

        //Provider 3
        DB::table('values')->insert([
            'user_id' => '6',
            'property_id' => '1',
            'value' => str_random(10),
        ]);
        
        DB::table('values')->insert([
            'user_id' => '6',
            'property_id' => '2',
            'value' => 'Dr.',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '6',
            'property_id' => '3',
            'value' => 'English;French',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '6',
            'property_id' => '4',
            'value' => '50',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '6',
            'property_id' => '5',
            'value' => str_random(50),
        ]);
        
        DB::table('values')->insert([
            'user_id' => '6',
            'property_id' => '6',
            'value' => '0',
        ]);
        
        //Patient 1
        DB::table('values')->insert([
            'user_id' => '7',
            'property_id' => '7',
            'value' => '1986',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '7',
            'property_id' => '8',
            'value' => '10',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '7',
            'property_id' => '9',
            'value' => '27',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '7',
            'property_id' => '10',
            'value' => '1',
        ]);
        
        //Patient 2
        DB::table('values')->insert([
            'user_id' => '8',
            'property_id' => '7',
            'value' => '1988',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '8',
            'property_id' => '8',
            'value' => '06',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '8',
            'property_id' => '9',
            'value' => '07',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '8',
            'property_id' => '10',
            'value' => '1',
        ]);
        
        //Patient 3        
        DB::table('values')->insert([
            'user_id' => '9',
            'property_id' => '7',
            'value' => '1977',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '9',
            'property_id' => '8',
            'value' => '07',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '9',
            'property_id' => '9',
            'value' => '07',
        ]);
        
        DB::table('values')->insert([
            'user_id' => '9',
            'property_id' => '10',
            'value' => '0',
        ]);


    }
}
