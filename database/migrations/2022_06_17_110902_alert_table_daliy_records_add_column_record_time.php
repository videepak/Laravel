<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableDaliyRecordsAddColumnRecordTime extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('daliy_records', function (Blueprint $table) {
            $table->timestamp('record_date')->nullable()->after('total_task');
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
            $table->dropColumn('record_date');
        });
    }
}
