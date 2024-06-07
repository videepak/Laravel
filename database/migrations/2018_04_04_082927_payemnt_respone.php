<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PayemntRespone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //Create table query
        Schema::create('payment_response', function (Blueprint $table) {
            $table->increments('id');
            $table->Integer('uid');
            $table->String('email');
            $table->String('paid_amount');
            $table->String('txn_id');
            $table->String('item_price_currency');
            $table->String('item_name');
            $table->Integer('item_number');
            $table->String('payment_status');
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
        //
    }
}
