<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertBarcodeNotesTableChangeDatatypeforReasonColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("barcode_notes",function($table){
            $table->string("reason")->change();
            $table->string("description")->after('reason');
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
            $table->integer("reason")->change();
            $table->dropColumn("description");
        });
    }
}
