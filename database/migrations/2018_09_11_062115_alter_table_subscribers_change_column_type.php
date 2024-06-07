<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSubscribersChangeColumnType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->date('sub_start_date')->nullable()->change();
            $table->date('sub_end_date')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('subscribers', function (Blueprint $table) {
            $table->string('sub_start_date')->change();
            $table->string('sub_end_date')->change();
        });
    }

}
