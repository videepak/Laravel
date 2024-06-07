<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertActivityLogTableAddColumnWastabdRecyle extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("activity_log",function($table){
            $table->string('wast')->after('barcode_id')->nullable();
            $table->string('recycle')->after('wast')->nullable();
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
            $table->dropColumn('wast');
            $table->dropColumn('recycle');
        });
    }
}
