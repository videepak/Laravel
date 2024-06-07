<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertAppPermissionsTableAddColumnDaliyTaskComplete extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('app_permissions', function (Blueprint $table) {
            $table->integer('daliy_task_complete')->after('checkin_pending')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('app_permissions', function (Blueprint $table) {
            $table->dropColumn('daliy_task_complete');
        });
    }
}
