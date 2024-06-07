<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTaskImagesTableAddMediaTypeColunm extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'task_images',
            function (Blueprint $table) {
                $table->string('media_type')->after('files_name')->nullable();
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
            'task_images',
            function (Blueprint $table) {
                $table->dropColumn('media_type');
            }
        );
    }
}
