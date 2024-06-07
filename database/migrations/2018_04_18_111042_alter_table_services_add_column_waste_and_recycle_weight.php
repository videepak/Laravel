<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableServicesAddColumnWasteAndRecycleWeight extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('services', function($table) {

            $table->float('waste_weight')->after('pickup_type');
            $table->float('recycle_weight')->after('pickup_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('services', function($table) {
            $table->dropColumn('waste_weight');
            $table->dropColumn('recycle_weight');
        });
    }

}
