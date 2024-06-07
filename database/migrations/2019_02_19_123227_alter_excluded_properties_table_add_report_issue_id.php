<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterExcludedPropertiesTableAddReportIssueId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('excluded_properties', function (Blueprint $table) {
            $table->integer('report_issue_id')->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('excluded_properties', function (Blueprint $table) {
            $table->dropColumn('report_issue_id');
        });
    }

}
