<?php

/**
 * Create table for user roles
 * e.g. Admin and normal user
 */

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->text('name');
            $table->timestamps();
        });
        // insert default roles 1=admin, 2=editor, 3=author!
        DB::table('roles')->insert(['name'=>'administrator']);
        DB::table('roles')->insert(['name'=>'editor']);
        DB::table('roles')->insert(['name'=>'author']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('roles');
    }
}
