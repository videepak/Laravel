<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterReportIssueTableAddExceptionDateColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->date('issue_date')->after('unit_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->dropColumn('issue_date');
        });
    }

}
