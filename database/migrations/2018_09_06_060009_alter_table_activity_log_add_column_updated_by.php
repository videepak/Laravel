<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableActivityLogAddColumnUpdatedBy extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() { 
        Schema::table('activity_log', function (Blueprint $table) {
            $table->integer('updated_by')->nullable()->after('longitude');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->dropColumn('updated_by');
        });
    }

}
