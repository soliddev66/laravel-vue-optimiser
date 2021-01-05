<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNetworkSettingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('network_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->double('group_1a')->nullable();
            $table->double('group_1b')->nullable();
            $table->double('group_2a')->nullable();
            $table->double('group_2b')->nullable();
            $table->double('group_3a')->nullable();
            $table->double('group_3b')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('network_settings');
    }
}
