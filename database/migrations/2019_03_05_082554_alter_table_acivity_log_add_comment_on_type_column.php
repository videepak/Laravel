<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableAcivityLogAddCommentOnTypeColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('activity_log', function (Blueprint $table) {
            DB::statement("ALTER TABLE activity_log MODIFY type INT COMMENT '1=Barcode,2=Pick,3=Violation,4=Unit,5=Rollback,6=Note Added,7=Check-In,8=walk-through';");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('activity_log', function (Blueprint $table) {
            DB::statement("ALTER TABLE activity_log MODIFY type INT COMMENT '1=Barcode,2=Pick,3=Violation,4=Unit,5=Rollback,6=Note Added,7=Check-In';");
        });
    }

}
