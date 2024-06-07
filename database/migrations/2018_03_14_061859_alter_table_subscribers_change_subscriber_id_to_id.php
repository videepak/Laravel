<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableSubscribersChangeSubscriberIdToId extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::table('subscribers', function(Blueprint $table) {
            $table->renameColumn('subscriber_id', 'id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::table('subscribers', function(Blueprint $table) {
            $table->renameColumn('id', 'subscriber_id');
        });
    }

}
