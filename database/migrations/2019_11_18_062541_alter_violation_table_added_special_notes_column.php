<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterViolationTableAddedSpecialNotesColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('violations', function (Blueprint $table) {
            $table->longText('special_note')->after('violation_action')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('violations', function (Blueprint $table) {
            $table->dropColumn('special_note');
        });
    }

}
