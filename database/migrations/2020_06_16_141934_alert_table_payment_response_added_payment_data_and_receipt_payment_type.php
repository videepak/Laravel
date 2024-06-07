<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTablePaymentResponseAddedPaymentDataAndReceiptPaymentType extends Migration
{
    /**
     * Run the migrations.
     * others
     * @return void
     */
    public function up()
    {
        Schema::table('payment_response', function (Blueprint $table) {
            $table->longText('receipt')->after("payment_status");
            $table->longText('payment_type')->after("receipt");
            $table->longText('payment_data')->after("payment_type");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_response', function (Blueprint $table) {
            $table->dropColumn('receipt');
            $table->dropColumn('payment_type');
            $table->dropColumn('payment_data');
        });
    }
}
