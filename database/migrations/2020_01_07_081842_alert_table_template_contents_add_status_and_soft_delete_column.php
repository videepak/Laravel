<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableTemplateContentsAddStatusAndSoftDeleteColumn extends Migration
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
                $table->integer('status')->after('content')
                        ->comment="1=active,0=deactive";
                $table->string('subject')->after('content');
                $table->softDeletes();
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
                $table->dropColumn('status');
                $table->dropColumn('subject');
                $table->dropSoftDeletes();
            }
        );
    }
}
