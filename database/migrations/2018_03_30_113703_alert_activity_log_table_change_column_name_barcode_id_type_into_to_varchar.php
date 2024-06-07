<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertActivityLogTableChangeColumnNameBarcodeIdTypeIntoToVarchar extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::table("activity_log",function($table){
            $table->string('barcode_id')->change();
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
            $table->interge('barcode_id')->change();
        });
    }
}
