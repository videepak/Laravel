<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableViolationsAddPropertyColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'violations',
            function (Blueprint $table) {
                $table->integer('property_id')->after('barcode_id');
                $table->integer('building_id')->after('property_id');
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
            'violations',
            function (Blueprint $table) {
                $table->dropColumn('property_id');
                $table->dropColumn('building_id');
            }
        );
    }
}
