<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableWalkThroughRecordAddActivityIdColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('walk_through_records', function($table) {
            $table->integer('activity_id')->after('building_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('walk_through_records', function($table) {
            $table->dropColumn('activity_id');
        });
    }

}
