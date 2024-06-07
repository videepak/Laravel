<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterAddColumnUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("users",function($table){
            $table->string('gender')->after('password');
            $table->string('image_name')->after('gender');
            $table->string('colourBlindMode')->after('image_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("users",function($table){
            $table->dropColumn('gender');
            $table->dropColumn('image');
            $table->dropColumn('colourBlindMode');
        });
    }
}
