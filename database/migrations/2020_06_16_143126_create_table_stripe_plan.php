<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableStripePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stripe_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('subscription_id');
            $table->integer('stripe_plan_id');
            $table->string('stripe_plan_name');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
       
         Schema::dropIfExists('stripe_plan');
       
    }
}
