<?php

use Illuminate\Database\Seeder;

class AppointmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // no notes
        DB::table('appointments')->insert([

            [
                'position_id' => 2,
                'provider_id' => 4,
                'patient_id' => 7,
                'time_slot_id' => 3,
                'address' => "263 Eglinton Ave W",
                'status' => 'confirmed',
            ],
            [
                'position_id' => 2,
                'provider_id' => 4,
                'patient_id' => 8,
                'time_slot_id' => 11,
                'address' => "263 Eglinton Ave W",
                'status' => 'confirmed',
            ],
            [
                'position_id' => 2,
                'provider_id' => 4,
                'patient_id' => 7,
                'time_slot_id' => 15,
                'address' => "263 Eglinton Ave W",
                'status' => 'confirmed',
            ],
            [
                'position_id' => 2,
                'provider_id' => 4,
                'patient_id' => 9,
                'time_slot_id' => 25,
                'address' => "263 Eglinton Ave W",
                'status' => 'confirmed',
            ],
            [
                'position_id' => 2,
                'provider_id' => 5,
                'patient_id' => 9,
                'time_slot_id' => 35,
                'address' => "263 Eglinton Ave W",
                'status' => 'confirmed',
            ],
            [
                'position_id' => 2,
                'provider_id' => 6,
                'patient_id' => 7,
                'time_slot_id' => 53,
                'address' => "263 Eglinton Ave W",
                'status' => 'cancelled',
            ],
            [
                'position_id' => 2,
                'provider_id' => 6,
                'patient_id' => 8,
                'time_slot_id' => 60,
                'address' => "263 Eglinton Ave W",
                'status' => 'confirmed',
            ],

        ]);

        // with notes
        DB::table('appointments')->insert([
            
            [
                'position_id' => 2,
                'provider_id' => 5,
                'patient_id' => 8,
                'time_slot_id' => 40,
                'status' => 'confirmed',
                'address' => "263 Eglinton Ave W",
                'note' => 'This is a note'
            ],
            [
                'position_id' => 2,
                'provider_id' => 6,
                'patient_id' => 8,
                'time_slot_id' => 26,
                'status' => 'confirmed',
                'address' => "263 Eglinton Ave W",
                'note' => 'This is a note'
            ],

        ]);
        
    }
}
