<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBarcodeNotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barcode_notes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('address1');
            $table->string('address2');
            $table->string('lat');
            $table->string('long');
            $table->integer('unit');
            $table->integer('reason');
            $table->longText('image');
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
        Schema::dropIfExists('barcode_notes');
    }
}
