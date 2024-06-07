<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoteSubjectsTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('note_subjects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('subject');
            $table->integer('user_id')->nullable();
            $table->integer('type')->default(0)->comment = '0=default';
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('note_subjects');
    }

}
