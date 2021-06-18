<?php

use Illuminate\Database\Seeder;

class AvailabilitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('availabilities')->insert([
            
            [
                'provider_id' => 4,
                'date' => '2018-01-25',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-01-25',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-01-27',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-01-27',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-02-01',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-02-01',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-01-03',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 60
            ],
            [
                'provider_id' => 4,
                'date' => '2018-01-03',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 60
            ],


            [
                'provider_id' => 5,
                'date' => '2018-01-25',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-25',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-27',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-27',
                'start' => '13:15',
                'end' => '18:00',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-02',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-02',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-05',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 5,
                'date' => '2018-01-05',
                'start' => '13:15',
                'end' => '18:00',
                'duration' => 75
            ],


            [
                'provider_id' => 6,
                'date' => '2018-01-25',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-25',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-27',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-27',
                'start' => '13:15',
                'end' => '18:00',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-17',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-17',
                'start' => '13:15',
                'end' => '18:30',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-03',
                'start' => '09:00',
                'end' => '12:00',
                'duration' => 75
            ],
            [
                'provider_id' => 6,
                'date' => '2018-01-03',
                'start' => '13:15',
                'end' => '18:00',
                'duration' => 75
            ],

        ]);
        
    }
}
