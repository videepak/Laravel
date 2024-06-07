<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertActivityLogTableChnageColumnNamePropertyIdToBarcodeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("activity_log",function($table){
            $table->renameColumn('property_id','barcode_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    { 
        Schema::table("activity_log",function($table){
            $table->renameColumn('barcode_id','property_id');
        });
    }
}
