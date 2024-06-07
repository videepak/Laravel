<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableViolationsAddCloumnMobileUniqeId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'violations',
            function (Blueprint $table) {
                $table->string('mobile_uniqe_id')->after('status');
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
            'violations',
            function (Blueprint $table) {
                $table->dropColumn('mobile_uniqe_id');
            }
        );
    }
}
