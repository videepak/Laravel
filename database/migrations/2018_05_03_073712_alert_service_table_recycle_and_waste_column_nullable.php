<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertServiceTableRecycleAndWasteColumnNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("services",function($table){
            $table->string("recycle_weight")->change()->nullable();
            $table->string("waste_weight")->change()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
          Schema::table("services",function($table){
                $table->string("recycle_weight")->change()->default(0);
                $table->string("waste_weight")->change()->default(0);
        });
    }
}
