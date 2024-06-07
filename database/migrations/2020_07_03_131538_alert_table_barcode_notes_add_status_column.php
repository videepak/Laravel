<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableBarcodeNotesAddStatusColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('barcode_notes', function (Blueprint $table) {
            $table->integer("status")
                  ->after("mobile_uniqe_id")
                  ->default(0)
                  ->comment="0=New,2=Submitted,5=Closed,6=Archived";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('barcode_notes', function (Blueprint $table) {
            $table->dropColumn("status");
        });
    }
}
