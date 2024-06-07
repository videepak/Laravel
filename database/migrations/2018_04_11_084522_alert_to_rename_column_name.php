<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertToRenameColumnName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("units",function($table){
            $table->renameColumn('building_no','building');
            $table->renameColumn('building_floor','floor');
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
            $table->renameColumn('building','building_no');
            $table->renameColumn('building_floor','building_floor');
        });
    }
}
