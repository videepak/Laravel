<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTableUserNotificationAddDayFrequencyColumn extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->integer('day_frequency')->default(0)->after('sms')->comment='1=daliy, 2=weekly, 3=monthly';

            DB::statement("ALTER TABLE user_notifications MODIFY type INT COMMENT '1=count violation, 2=count notes, 3=property chek-in, 4=first pickup,5=create violation,6=clock in/out delay push notification,7=Automated Service Report'");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_notifications', function (Blueprint $table) {
            $table->dropColumn('day_frequency');

            DB::statement("ALTER TABLE user_notifications MODIFY type INT COMMENT '1=count violation, 2=count notes, 3=property chek-in, 4=first pickup,5=create violation,6=clock in/out delay push notification'");
        });
    }
}
