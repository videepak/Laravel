<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableActivityLogAddColumnType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('activity_log',function($table){
            $table->integer('type')->nullable()->after("barcode_id")->comment = "1=Barcode,2=Pick,3=Violation,4=Unit";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('activity_log',function($table){
            $table->dropColumn('type');
        });
    }
}
