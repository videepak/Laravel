<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDaliyRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'daliy_records',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('property_id');
                $table->integer('pickup_completed');
                $table->integer('active_units');
                $table->integer('route_checkpoints_scanned');
                $table->integer('checkpoints_by_property');
                $table->integer('building_walk_throughs');
                $table->integer('active_building');
                $table->time('checkinout_duration');
                $table->integer('total_tasks_completed');
                $table->integer('missed_property_checkouts');
                $table->timestamps();
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
        Schema::dropIfExists('daliy_records');
    }
}
