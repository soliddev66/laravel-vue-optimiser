<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountNameAndAdvertisersIntoUserProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_providers', function (Blueprint $table) {
            $table->string('account_name')->after('open_id')->nullable();
            $table->text('advertisers')->after('account_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_providers', function (Blueprint $table) {
            $table->dropColumn('account_name');
            $table->dropColumn('advertisers');
        });
    }
}
