<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlertTableServiceTableChangeDataTypePickupStartAndpickupFinishColumn extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('services', function($table) {
            $table->timestamps();
        });

        DB::statement('ALTER TABLE services MODIFY pickup_start  DATETIME');
        DB::statement('ALTER TABLE services MODIFY pickup_finish  DATETIME');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {

        Schema::table('services', function($table) {
            $table->dropColumn('created_at');
            $table->dropColumn('updated_at');
        });

        DB::statement('ALTER TABLE services MODIFY pickup_start  DATE');
        DB::statement('ALTER TABLE services MODIFY pickup_finish  DATE');
    }

}
