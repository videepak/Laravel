<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaskImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'task_images',
            function (
                Blueprint $table) {
                    $table->increments('id');
                    $table->string('files_name');
                    $table->integer('task_id');
                    $table->integer('activity_id');
                    $table->timestamps();
            }
        );

        Schema::table(
            'activity_log',
            function (
                Blueprint $table) {
                    $table->integer('type')
                        ->change()
                        ->comment = "1=Barcode,2=Pick,3=Violation,4=Unit,5=Rollback,6=Note Added,7=Check-In,8=walk-through, 9=clock-in, 10=clock-out, 11=Route check point, 12=Redundant Route Service, 13=Task completeed";
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
        Schema::dropIfExists('task_images');
    }
}
