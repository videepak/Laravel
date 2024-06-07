<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableBuildingAddColumnPropertyId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('buildings', function (Blueprint $table) {
            $table->integer('property_id')->after('unit_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('buildings', function (Blueprint $table) {
            $table->dropColumn('property_id');
        });
    }

}
