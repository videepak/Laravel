<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableActivityLogChangeTheCommentOfTypeCloumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'activity_log',
            function (Blueprint $table)
            {
                $table->integer('type')->comment('1=Barcode,2=Pick,3=Violation,4=Unit,5=Rollback,6=Note Added,7=Check-In,8=walk-through, 9=clock-in, 10=clock-out')->change();
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
            'activity_log',
            function (Blueprint $table)
            {
                $table->integer('type')->comment('1=Barcode,2=Pick,3=Violation,4=Unit,5=Rollback,6=Note Added,7=Check-In,8=walk-through')->change();
            }
        );
    }
}
