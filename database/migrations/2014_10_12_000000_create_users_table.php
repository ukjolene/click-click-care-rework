<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
        CREATE FUNCTION GEODIST(lat1 DOUBLE, lon1 DOUBLE, lat2 DOUBLE, lon2 DOUBLE) RETURNS double
            NO SQL
            DETERMINISTIC
        BEGIN
            DECLARE dist DOUBLE;
            SET dist =  round(acos(cos(radians(lat1))*cos(radians(lon1))*cos(radians(lat2))*cos(radians(lon2)) + cos(radians(lat1))*sin(radians(lon1))*cos(radians(lat2))*sin(radians(lon2)) + sin(radians(lat1))*sin(radians(lat2))) * 6378.8, 1);
                   RETURN dist;
        END;
        ");
        
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('role');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('first_name');
            $table->string('last_name');
            $table->enum('gender', ['male', 'female']); 
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('postal_code', 7)->nullable();
            $table->double('latitude', 17, 14)->nullable();
            $table->double('longitude', 17, 14)->nullable();
            $table->string('phone_number', 10)->nullable(); 
            $table->enum('approved', ['approved', 'disapproved', 'pending'])->default('pending'); 
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('role_id')->references('id')->on('roles');  
        });

        DB::table('roles')->insert([
            ['role' => 'admin'],
            ['role' => 'provider'],
            ['role' => 'patient']
        ]);
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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('users_role_id_foreign');
        });
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
        DB::unprepared('DROP FUNCTION IF EXISTS GEODIST');
		DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
