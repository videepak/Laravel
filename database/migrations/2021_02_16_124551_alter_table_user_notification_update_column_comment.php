<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserNotificationUpdateColumnComment extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->integer('type')->comment('1=count violation, 2=count notes, 3=property chek-in, 4=first pickup,5=create violation,6=clock in/out delay push notification,7=Automated Service Report,8=Automated Unit Report')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->integer('type')->comment('1=count violation, 2=count notes, 3=property chek-in, 4=first pickup,5=create violation,6=clock in/out delay push notification,7=Automated Service Report')->change();
        });
    }
}
