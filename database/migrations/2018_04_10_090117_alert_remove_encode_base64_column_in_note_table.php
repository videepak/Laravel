<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertRemoveEncodeBase64ColumnInNoteTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::table("barcode_notes",function($table){
            $table->dropColumn("image");
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
            $table->longText("image")->after('description')->nullable();
        });
    }
}
