<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableReportIssuesAddColumnStatus extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->integer('status')->after('type')->default(0)->comment = "0=pending,1=reported";
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('report_issues', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }

}
