<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertBarcodeNotesTableSetUnitColumnsDefaultValueNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("barcode_notes",function($table){
            $table->string("unit")->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("barcode_notes",function($table){
            $table->string("unit")->nullable()->change();
        });
    }
}
