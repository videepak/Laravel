<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableViolationToChangeTheCommentOfStatusColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        DB::statement("ALTER TABLE violations MODIFY status INT COMMENT '0=New,2=Submitted,3=Discarded,4=Pending,5=Closed,6=Archived';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
         DB::statement("ALTER TABLE violations MODIFY status INT COMMENT 'id of employees';");
    }

}
