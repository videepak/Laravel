<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertNotesTableAddUserIdColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table("barcode_notes",function($table){
            $table->integer("user_id")->after('image');
            $table->integer("activityLogId")->after("reason");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table("barcode_notes",function($table){
            $table->dropColumn("user_id");
        });
    }
}
