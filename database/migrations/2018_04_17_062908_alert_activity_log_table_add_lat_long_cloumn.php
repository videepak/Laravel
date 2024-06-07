<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertActivityLogTableAddLatLongCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table("activity_log",function($table){
            $table->string('latitude')->after('barcode_id')->nullable();
            $table->string('longitude')->after('latitude')->nullable();
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
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
}
