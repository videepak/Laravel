<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsUserToTemplateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('template_contents', function (Blueprint $table) {
            $table->integer('is_user')->nullable()->after('user_id')->comment='1=subscriber,2=property manager';
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('template_contents', function (Blueprint $table) {
            //
        });
    }
}
