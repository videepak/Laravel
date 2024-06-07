<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'user_notifications',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id');
                $table->integer('subscriber_id');
                $table->integer('email')->default(0);
                $table->integer('sms')->default(0);
                $table->integer('type')->comment = '1=count violation, 2=count notes, 3=property chek-in, 4=first pickup,5=create violation,6=clock in/out delay push notification';
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
        Schema::dropIfExists('user_notifications');
    }
}
