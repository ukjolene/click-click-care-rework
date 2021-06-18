<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->increments('id');
            $table->string('property');
            $table->string('validation_rules');
            $table->timestamps();
        });


        Schema::create('rel_role_property', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->integer('property_id')->unsigned();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('property_id')->references('id')->on('properties');

        });

        Schema::create('values', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('property_id')->unsigned();
            $table->string('value', 1000);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('property_id')->references('id')->on('properties');

        });

/** Property table seed */
        DB::table('properties')->insert(
            array(
                'property' => 'license_number',
                'validation_rules' => 'required_if:role_id,2'
            )
        );

        DB::table('properties')->insert(
            array(
                'property' => 'title',
                'validation_rules' => 'required_if:role_id,2'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'language',
                'validation_rules' => 'required_if:role_id,2'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'distance',
                'validation_rules' => 'required_if:role_id,2'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'description',
                'validation_rules' => 'required_if:role_id,2'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'privatepatient',
                'validation_rules' => 'required_if:role_id,2'
            )
        );

        DB::table('properties')->insert(
            array(
                'property' => 'YOB',
                'validation_rules' => 'required_if:role_id,3'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'MOB',
                'validation_rules' => 'required_if:role_id,3'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'DOB',
                'validation_rules' => 'required_if:role_id,3'
            )
        );  

        DB::table('properties')->insert(
            array(
                'property' => 'healthcard',
                'validation_rules' => 'required_if:role_id,3'
            )
        );  

/** Role - Property relation table seed */
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '2',
                'property_id' => '1'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '2',
                'property_id' => '2'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '2',
                'property_id' => '3'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '2',
                'property_id' => '4'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '2',
                'property_id' => '5'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '2',
                'property_id' => '6'
            )
        );  

        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '3',
                'property_id' => '7'
            )
        );
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '3',
                'property_id' => '8'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '3',
                'property_id' => '9'
            )
        );  
        DB::table('rel_role_property')->insert(
            array(
                'role_id' => '3',
                'property_id' => '10'
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
		Eloquent::unguard();
		DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('values');
        Schema::dropIfExists('rel_role_property');
        Schema::dropIfExists('properties');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}