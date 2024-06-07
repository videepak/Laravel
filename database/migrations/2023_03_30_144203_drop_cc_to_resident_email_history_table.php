<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropCcToResidentEmailHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resident_email_history', function (Blueprint $table) {
            $table->dropColumn('cc');
            $table->dropColumn('unit_id');
            $table->dropColumn('property_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resident_email_history', function (Blueprint $table) {
            //
        });
    }
}
