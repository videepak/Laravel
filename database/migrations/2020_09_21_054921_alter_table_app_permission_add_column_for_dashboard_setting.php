<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAppPermissionAddColumnForDashboardSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'app_permissions',
            function (Blueprint $table) {
                $table->integer('recycling_collected')->default(0)->after('manual_pickup');
                $table->integer('units_serviced')->default(0)->after('recycling_collected');
                $table->integer('violation')->default(0)->after('units_serviced');
                $table->integer('checkin_pending')->default(0)->after('violation');
                $table->integer('user_id')->default(0)->after('checkin_pending');
                $table->integer('manual_pickup')->default(0)->change();
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
            'app_permissions',
            function (Blueprint $table) {
                $table->dropColumn('recycling_collected');
                $table->dropColumn('units_serviced');
                $table->dropColumn('violation');
                $table->dropColumn('checkin_pending');
                $table->dropColumn('user_id');
            }
        );
    }
}
