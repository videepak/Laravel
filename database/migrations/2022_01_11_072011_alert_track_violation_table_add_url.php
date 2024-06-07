<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTrackViolationTableAddUrl extends Migration
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
                $table->string("url", 1024)->nullable()->after('type');
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
                $table->dropColumn("url");
            }
        );
    }
}
