<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSubscriberAddColumnSubscriptionType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriber', function($table) {
            $table->integer('subscription_id');
            $table->integer('auto_renew');
            $table->string('sub_start_date');         
            $table->string('sub_end_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriber', function($table) {
            $table->dropColumn('subscription_id');
            $table->dropColumn('auto_renew');
            $table->dropColumn('sub_start_date');
            $table->dropColumn('sub_end_date');
           
        });
    }
}
