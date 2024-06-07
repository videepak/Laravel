<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMoveindateToResidentsUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('residents_unit', function (Blueprint $table) {
            $table->date('move_in_date')->after('violation_id');
            $table->date('move_out_date')->after('move_in_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('residents_unit', function (Blueprint $table) {
            //
        });
    }
}
