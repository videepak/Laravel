<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertUnitsTableForUnitNumberColumnSetDefaultVlaue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('units',function($table){
           $table->integer('unit_number')->nullable()->change(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units',function($table){
           $table->integer('unit_number')->nullable(false)->change(); 
        });
    }
}
