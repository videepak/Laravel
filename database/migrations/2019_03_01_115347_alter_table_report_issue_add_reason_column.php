<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableReportIssueAddReasonColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->integer('issue_reason_id')->after('type')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->dropColumn('issue_reason_id');
        });
    }

}
