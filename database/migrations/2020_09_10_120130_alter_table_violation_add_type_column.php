<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableViolationAddTypeColumn extends Migration
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
                $table->string('type')
                    ->after('manager_status')
                    ->default(0)
                    ->comment = '1 = Route Check Point Violation; 0 = Property Violation';
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
                $table->dropColumn('type');
            }
        );
    }
}
