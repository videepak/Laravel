<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableViolationAddedColumnStatus extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::table('violations',function($table){
            $table->integer('status')->after('barcode_id')->default(0)->comment='0=not complete,1=complete'; 
            $table->integer('activity_id')->after('barcode_id'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
        Schema::table('violations',function($table){
            $table->dropColumn('status'); 
            $table->dropColumn('activity_id'); 
        });
    }
}
