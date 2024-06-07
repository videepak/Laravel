<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableBuildingSetNullableDefaultValueUnitNumberColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('buildings', function (Blueprint $table) {
            $table->integer('unit_number')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('buildings', function (Blueprint $table) {
            $table->integer('unit_number')->default('0')->change();
        });
    }

}
