<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableUsersAddClockInClockOutCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->time("service_in_time")->nullable()->after("image_name");
            $table->time("service_out_time")->nullable()->after("service_in_time");
            $table->integer("reporting_manager_id")->default(0)->after("user_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn("service_in_time");
            $table->dropColumn("service_out_time");
            $table->dropColumn("reporting_manager_id");
        });
    }
}
