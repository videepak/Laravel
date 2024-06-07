<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserPropertiesAddColumnType extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('user_properties', function (Blueprint $table) {
            $table->integer('type')->after('status')->default('1')->comment = '1=empolyee,2=Property Manager';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('user_properties', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }

}
