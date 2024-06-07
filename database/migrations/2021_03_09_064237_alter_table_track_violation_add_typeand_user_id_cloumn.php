<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTrackViolationAddTypeandUserIdCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'track_violations',
            function (Blueprint $table) {
                $table->integer('user_id')->after('violation_response')->default('0');
                $table->integer('type')->after('user_id')->default('0')->comment = "0=violation,1=check-in";
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
            'track_violations',
            function (Blueprint $table) {
                $table->dropColumn('user_id');
                $table->dropColumn('type');
            }
        );
    }
}
