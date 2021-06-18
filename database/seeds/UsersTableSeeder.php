<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'role_id' => 1,
            'email' => 'ye@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Ye',
            'last_name' => 'Yuan',
            'gender' => 'male',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 1,
            'email' => 'gord@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Gord',
            'last_name' => 'Schnurr',
            'gender' => 'male',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 1,
            'email' => 'cedric@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Irfaan',
            'last_name' => 'Auhammad',
            'gender' => 'male',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);


        DB::table('users')->insert([
            'role_id' => 2,
            'email' => 'provider1@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'First',
            'last_name' => 'Provider',
            'gender' => 'male',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 2,
            'email' => 'provider2@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Second',
            'last_name' => 'Provider',
            'gender' => 'male',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 2,
            'email' => 'provider3@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Third',
            'last_name' => 'Provider',
            'gender' => 'female',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 3,
            'email' => 'patient1@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'First',
            'last_name' => 'Patient',
            'gender' => 'female',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 3,
            'email' => 'patient2@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Second',
            'last_name' => 'Patient',
            'gender' => 'female',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('users')->insert([
            'role_id' => 3,
            'email' => 'patient3@simplistics.ca',
            'password' => bcrypt('test1234'),
            'first_name' => 'Third',
            'last_name' => 'Patient',
            'gender' => 'female',
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
            'phone_number' => '4163479883',
            'approved' => 'approved'
        ]);

        DB::table('offices')->insert([
            'user_id' => 4,
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
        ]);

        DB::table('offices')->insert([
            'user_id' => 5,
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
        ]);

        DB::table('offices')->insert([
            'user_id' => 6,
            'address' => '263 Eglinton Ave W',
            'city' => 'Toronto',
            'province' => 'ON',
            'postal_code' => 'M4R 1B1',
            'latitude' => '43.46432760000000',
            'longitude' => '-79.73989590000000',
        ]);
    }
}
