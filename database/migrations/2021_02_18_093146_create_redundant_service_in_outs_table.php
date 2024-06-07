<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRedundantServiceInOutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redundant_service_in_outs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('subscriber_id');
            $table->integer('property_id');
            $table->dateTime('service_in');
            $table->dateTime('service_out')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('redundant_service_in_outs');
    }
}
