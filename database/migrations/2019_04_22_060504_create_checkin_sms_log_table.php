<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCheckinSmsLogTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('check_in_sms_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('message');
            $table->String('receiver_id');
            $table->Integer('sender_id');
            $table->Integer('property_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('check_in_sms_logs');
    }

}
