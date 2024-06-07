<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('subscriber_id')->unsigned();
            $table->integer('category_id')->nullable()->unsigned();
            $table->string('ticket_id')->unique();
            $table->string('title')->nullable();
            $table->string('priority')->nullable();
            $table->longText('message');
            $table->string('files_name')->nullable();
            $table->string('files_type')->nullable();
            $table->integer('status')->default(0)->comment="0=not started,1=in progress,2=closed,3=archived";
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
        Schema::dropIfExists('tickets');
    }
}
