<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('services',function($table){
            $table->date('pickup_start')->change();
            $table->date('pickup_finish')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('services',function($table){
            $table->dateTime('pickup_start')->change();
            $table->dateTime('pickup_finish')->change();
        });
    }
}
