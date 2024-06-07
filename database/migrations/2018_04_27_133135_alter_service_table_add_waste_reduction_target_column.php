<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterServiceTableAddWasteReductionTargetColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("services",function($table){
            $table->integer("waste_reduction_target")->nullable()->after("waste_weight");
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
            $table->dropColumn("waste_reduction_target");
        });
            
        }
}
