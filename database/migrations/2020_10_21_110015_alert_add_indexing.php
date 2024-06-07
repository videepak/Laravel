<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlertAddIndexing extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'route_check_ins',
             function (Blueprint $table) {
                 $table->index('barcode_id');
                 $table->index('building_id');
                 $table->index('property_id');
             }
        );

        Schema::table(
            'violation_images',
             function (Blueprint $table) {
                 $table->index('violation_id');
             }
        );

        Schema::table(
            'user_notifications',
             function (Blueprint $table) {
                 $table->index('user_id');
                 $table->index('subscriber_id');
             }
        );

        Schema::table(
            'report_issues',
             function (Blueprint $table) {
                 $table->index('property_id');
                 $table->index('building_id');
                 $table->index('unit_id');
                 $table->index('issue_reason_id');
                 $table->index('user_id');
                 $table->index('subscribers_id');
             }
        );

        Schema::table(
            'issue_reasons',
             function (Blueprint $table) {
                 $table->index('user_id');
             }
        );

        Schema::table(
            'customers',
             function (Blueprint $table) {
                 $table->index('user_id');
             }
        );

        Schema::table(
            'clock_in_outs',
             function (Blueprint $table) {
                 $table->index('user_id');
                 $table->index('activity_id');
             }
        );

        Schema::table(
            'app_permissions',
             function (Blueprint $table) {
                 $table->index('user_id');
                 $table->index('subscriber_id');
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
            'route_check_ins',
            function (Blueprint $table) {
                $table->dropIndex('route_check_ins_barcode_id_index');
                $table->dropIndex('route_check_ins_building_id_index');
                $table->dropIndex('route_check_ins_property_id_index');
            }
        );

        Schema::table(
            'violation_images',
             function (Blueprint $table) {
                 $table->index('violation_images_violation_id_index');
             }
        );

        Schema::table(
            'user_notifications',
             function (Blueprint $table) {
                 $table->index('user_notifications_user_id_index');
                 $table->index('user_notifications_subscriber_id_index');
             }
        );

        Schema::table(
            'report_issues',
             function (Blueprint $table) {
                 $table->index('report_issues_property_id_index');
                 $table->index('report_issues_building_id_index');
                 $table->index('report_issues_unit_id_index');
                 $table->index('issue_reason_id_index');
                 $table->index('report_issues_user_id_index');
                 $table->index('report_issues_subscribers_id_index');
             }
        );

        Schema::table(
            'issue_reasons',
             function (Blueprint $table) {
                 $table->index('issue_reasons_user_id_index');
             }
        );

        Schema::table(
            'customers',
             function (Blueprint $table) {
                 $table->index('customers_user_id_index');
             }
        );

        Schema::table(
            'clock_in_outs',
             function (Blueprint $table) {
                 $table->index('clock_in_outs_user_id_index');
                 $table->index('clock_in_outs_activity_id_index');
             }
        );

        Schema::table(
            'app_permissions',
             function (Blueprint $table) {
                 $table->index('app_permissions_user_id_index');
                 $table->index('app_permissions_subscriber_id_index');
             }
        );
    }
}
