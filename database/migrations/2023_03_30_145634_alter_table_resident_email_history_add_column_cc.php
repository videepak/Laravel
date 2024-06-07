<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableResidentEmailHistoryAddColumnCc extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('resident_email_history', function (Blueprint $table) {
            $table->string('cc')->after('subject')->nullable()->default(NULL);
            $table->string('unit_id')->after('resident_id')->nullable()->default(NULL);
            $table->integer('property_id')->after('unit_id')->nullable()->default(NULL);
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
            $table->dropColumn('cc');
        });
    }
}
