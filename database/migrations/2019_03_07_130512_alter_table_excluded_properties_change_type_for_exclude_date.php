<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableExcludedPropertiesChangeTypeForExcludeDate extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('excluded_properties', function($table) {
            $table->datetime('exclude_date')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('excluded_properties', function($table) {
            $table->date('exclude_date')->change();
        });
    }

}
