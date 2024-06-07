<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableSubscribersAddClockInClockOutCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->time("service_in_time")->default("06:00:00")->after("user_id");
            $table->time("service_out_time")->default("05:59:59")->after("service_in_time");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropColumn("service_in_time");
            $table->dropColumn("service_out_time");
        });
    }
}
