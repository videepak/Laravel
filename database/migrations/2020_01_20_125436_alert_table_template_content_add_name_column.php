<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableTemplateContentAddNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'template_contents',
            function (Blueprint $table) {
                $table->string('name')->after("subscriber_id");
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
            'template_contents',
            function (Blueprint $table) {
                $table->dropColumn('name');
            }
        );
    }
}
