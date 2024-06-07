<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableUsersToSomeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('job_title')->after("extension");
            $table->string('company')->after("job_title");
            $table->string('field_employee')->after("company");
            $table->string('country')->after("field_employee");
            $table->string('trial')->after("country");
            $table->date('trial_start')->after("trial");
            $table->date('trial_end')->after("trial_start");
            $table->string('subscriber_menu')->after("trial_end");
            $table->integer('stripe_id')->after("subscriber_menu");
            $table->integer('plan_id')->after("stripe_id");
            $table->integer('stripe_subscriber_id')->after("stripe_id");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('job_title');
            $table->dropColumn('company');
            $table->dropColumn('field_employee');
            $table->dropColumn('country');
            $table->dropColumn('trial');
            $table->dropColumn('trial_start');
            $table->dropColumn('trial_end');
            $table->dropColumn('subscriber_menu');
            $table->dropColumn('stripe_id');
            $table->dropColumn('plan_id');
            $table->dropColumn('stripe_subscriber_id');
        });
    }
}
