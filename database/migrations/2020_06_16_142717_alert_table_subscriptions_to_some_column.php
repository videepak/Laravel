<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableSubscriptionsToSomeColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->longText('features')->after("package_qr_code");
            $table->longText('star_features')->after("features");
            $table->longText('plan_content')->after("star_features");
            $table->longText('number_of_property')->after("plan_content");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('features');
            $table->dropColumn('star_features');
            $table->dropColumn('plan_content');
            $table->dropColumn('number_of_property');
        });
    }
}
