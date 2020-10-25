<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotificationPreferencesToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('volunteer_preferences')->after('phone')->default(false);
            $table->boolean('notify_food_drive')->after('phone')->default(false);
            $table->string('street_address')->after('notify_food_drive')->nullable();
            $table->string('city')->after('notify_food_drive')->nullable();
            $table->string('state')->after('notify_food_drive')->nullable();
            $table->string('postal_code')->after('notify_food_drive')->nullable();
            $table->string('travel_distance')->after('notify_food_drive')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('volunteer_preferences');
            $table->dropColumn('notify_food_drive');
            $table->dropColumn('street_address');
            $table->dropColumn('city');
            $table->dropColumn('state');
            $table->dropColumn('postal_code');
            $table->dropColumn('travel_distance');
        });
    }
}
