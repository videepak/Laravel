<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableBarcodeNotesAddColumnNotesType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('barcode_notes', function (Blueprint $table) {
            $table->integer('notes_type')->nullable()->after('manager_status')->comment='1=Unit Specific Notes,2=General Note,3=Checkout Notes';
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
            $table->dropColumn('notes_type');
            
        });
    }
}
