<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTwitterReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('twitter_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('campaign_id')->unsigned();
            $table->string('granularity')->nullable();
            $table->string('placement')->nullable();
            $table->longText('data')->nullable();
            $table->date('start_time')->nullable();
            $table->date('end_time')->nullable();
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
        Schema::dropIfExists('twitter_reports');
    }
}
