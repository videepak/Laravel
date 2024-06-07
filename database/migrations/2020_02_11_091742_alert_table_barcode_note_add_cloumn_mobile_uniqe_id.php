<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableBarcodeNoteAddCloumnMobileUniqeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'barcode_notes',
            function (Blueprint $table) {
                $table->string('mobile_uniqe_id')->after('user_id');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'barcode_notes',
            function (Blueprint $table) {
                $table->dropColumn('mobile_uniqe_id');
            }
        );
    }
}
