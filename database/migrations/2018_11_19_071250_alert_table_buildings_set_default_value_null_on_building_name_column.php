<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableBuildingsSetDefaultValueNullOnBuildingNameColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('building_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('buildings', function (Blueprint $table) {
            $table->string('building_name')->nullable()->change();
        });
    }

}
