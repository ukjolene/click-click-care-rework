<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {     
        $this->call(UsersTableSeeder::class);
        $this->call(ValuesTableSeeder::class);
        $this->call(PositionsTableSeeder::class);
        $this->call(QualificationsTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(MessagesTableSeeder::class);
        $this->call(AvailabilitiesTableSeeder::class);
        $this->call(TimeslotsTableSeeder::class);
        $this->call(AppointmentsTableSeeder::class);
    }
}
