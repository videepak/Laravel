<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertIssueReasonsChangeDataTypeForReasonColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'issue_reasons',
            function (Blueprint $table) {
                $table->longText('reason')->change();
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
            'issue_reasons',
            function (Blueprint $table) {
                $table->string('reason')->change();
            }
        );
    }
}
