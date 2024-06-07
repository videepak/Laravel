<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableTaskAssignSetPropertyIdColumnIsNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('task_assigns', function (Blueprint $table) {
            DB::statement("ALTER TABLE `task_assigns` CHANGE `property_id` `property_id` INT(11) NULL DEFAULT NULL;
            ");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('task_assgins', function (Blueprint $table) {
            DB::statement("ALTER TABLE `task_assigns` CHANGE `property_id` `property_id` INT(11) NULL DEFAULT NONE;
            ");
        });
    }
}
