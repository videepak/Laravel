<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertPropertiesCheckInTableAddReasonColum extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'properties_check_in',
            function (Blueprint $table)
            {
                $table->longText('reason')
                    ->after('check_in_complete')
                    ->nullable();
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
            'properties_check_in',
            function (Blueprint $table) {
                $table->dropColumn('column');
            }
        );
    }
}
