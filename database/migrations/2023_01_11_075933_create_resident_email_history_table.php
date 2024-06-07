<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResidentEmailHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resident_email_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('property_manager_id');
            $table->integer('resident_id');
            $table->string('unit_id');
            $table->integer('property_id');
            $table->string('subject');
            $table->longText('cc');
            $table->longText('body');
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
        Schema::dropIfExists('resident_email_history');
    }
}
