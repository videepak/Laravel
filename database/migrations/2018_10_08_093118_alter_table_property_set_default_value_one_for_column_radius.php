<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTablePropertySetDefaultValueOneForColumnRadius extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('properties', function (Blueprint $table) {
            $table->integer('radius')->default(1)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('properties', function (Blueprint $table) {
            $table->boolean('radius')->default(NULL)->change();
        });
    }

}
