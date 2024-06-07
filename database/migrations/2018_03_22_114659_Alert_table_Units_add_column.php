<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableUnitsAddColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('units', function($table)
        {
            $table->dateTime('last_scan_date')->nullable()->after('property_id');
            $table->string('barcode_id')->nullable()->after('property_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('property_id');
            $table->decimal('latitude', 10, 7)->nullable()->after('property_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('last_scan_date');
            $table->dropColumn('barcode_id');
            $table->dropColumn('longitude');
            $table->dropColumn('latitude');
        });
    }
}
