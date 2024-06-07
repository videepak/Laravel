<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableViolationImageAddOriginalNameColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'violation_images',
            function (Blueprint $table) {
                $table->string('original_name')->nullable()->after("filename");
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
            'violation_images',
            function (Blueprint $table) {
                $table->dropColumn('original_name');
            }
        );
    }
}
