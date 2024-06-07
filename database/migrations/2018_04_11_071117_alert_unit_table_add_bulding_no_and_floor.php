<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertUnitTableAddBuldingNoAndFloor extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("units",function($table){
            $table->string('building_no')->after('longitude')->nullable();
            $table->string('building_floor')->after('building_no')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("units",function($table){
            $table->dropColumn('building_no');
            $table->dropColumn('building_floor');
        });
    }
}
