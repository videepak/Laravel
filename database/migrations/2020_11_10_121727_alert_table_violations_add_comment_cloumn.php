<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableViolationsAddCommentCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'violations',
            function (Blueprint $table) {
                $table->longText('comment')->after('barcode_id')->nullable();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(
            'violations',
            function (Blueprint $table) {
                $table->dropColumn('comment');
            }
        );
    }
}
