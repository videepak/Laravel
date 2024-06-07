<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertUnitsTableSetColumnDefaultVlaue extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('units',function($table){
           $table->string('address1')->nullable()->change(); 
           $table->string('address2')->nullable()->change(); 
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
           $table->string('address1')->nullable(false)->change(); 
           $table->string('address2')->nullable(false)->change();  
        });
    }
}
