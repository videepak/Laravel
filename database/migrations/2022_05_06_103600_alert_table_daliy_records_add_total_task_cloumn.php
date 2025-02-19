<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableDaliyRecordsAddTotalTaskCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daliy_records', function (Blueprint $table) {
            $table->integer('total_task')->nullable()->after('missed_property_checkouts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('daliy_records', function (Blueprint $table) {
            $table->dropColumn('total_task');
        });
    }
}
