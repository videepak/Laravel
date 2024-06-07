<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableTasksAddColumnNotifyPropertyManager extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'tasks',
            function (Blueprint $table) {
                $table->integer('notify_property_manager')->after('is_photo')->default(0);
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
            'tasks',
            function (Blueprint $table) {
                $table->dropColumn('notify_property_manager');
            }
        );
    }
}
