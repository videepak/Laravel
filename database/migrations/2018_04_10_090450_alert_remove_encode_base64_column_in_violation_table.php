<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertRemoveEncodeBase64ColumnInViolationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       
       Schema::table("violations",function($table){
            $table->dropColumn("violation_image");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("violations",function($table){
            $table->longText("violation_image")->after('violation_action')->nullable();
        });
    }
}
