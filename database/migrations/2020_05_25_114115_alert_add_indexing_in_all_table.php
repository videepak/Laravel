<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertAddIndexingInAllTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('walk_through_records', function (Blueprint $table) {
            $table->index('activity_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->index('customer_id');
            $table->index('user_id');
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->index('subscription_id');
        });

        Schema::table('properties_check_in', function (Blueprint $table) {
            $table->index('property_id');
            $table->index('user_id');
            $table->index('check_in');
        });

        Schema::table('excluded_properties', function (Blueprint $table) {
            $table->index('property_id');
            $table->index('building_id');
            $table->index('unit_id');
            $table->index('report_issue_id');   
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('walk_through_records', function (Blueprint $table) {
            $table->dropIndex('walk_through_records_activity_id_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_customer_id_index');
            $table->dropIndex('users_user_id_index');
        });

        Schema::table('subscribers', function (Blueprint $table) {
            $table->dropIndex('subscribers_subscription_id_index');
        });

        Schema::table('properties_check_in', function (Blueprint $table) {
             $table->dropIndex('properties_check_in_property_id_index');
             $table->dropIndex('properties_check_in_user_id_index');
             $table->dropIndex('properties_check_in_check_in_index');
        });

         Schema::table('excluded_properties', function (Blueprint $table) {
             $table->dropIndex('excluded_properties_property_id_index');
             $table->dropIndex('excluded_properties_building_id_index');
             $table->dropIndex('excluded_properties_unit_id_index');
             $table->dropIndex('excluded_properties_report_issue_id_index');
        });
    }
}
