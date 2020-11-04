<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('redtrack_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('campaign_id');
            $table->double('approved')->nullable();
            $table->double('attribution')->nullable();
            $table->double('baddevice')->nullable();
            $table->double('blacklist')->nullable();
            $table->double('clicks')->nullable();
            $table->double('conversions')->nullable();
            $table->double('convtype1')->nullable();
            $table->double('convtype2')->nullable();
            $table->double('convtype3')->nullable();
            $table->double('convtype4')->nullable();
            $table->double('convtype5')->nullable();
            $table->double('convtype6')->nullable();
            $table->double('convtype7')->nullable();
            $table->double('convtype8')->nullable();
            $table->double('convtype9')->nullable();
            $table->double('convtype10')->nullable();
            $table->double('cost')->nullable();
            $table->double('cpa')->nullable();
            $table->double('cpc')->nullable();
            $table->double('cpt')->nullable();
            $table->double('cr')->nullable();
            $table->double('ctr')->nullable();
            $table->double('datacenter')->nullable();
            $table->double('declined')->nullable();
            $table->double('epc')->nullable();
            $table->double('hour_of_day')->nullable();
            $table->double('impressions')->nullable();
            $table->double('impressions_visible')->nullable();
            $table->double('lp_clicks')->nullable();
            $table->double('lp_ctr')->nullable();
            $table->double('lp_views')->nullable();
            $table->double('ok')->nullable();
            $table->double('other')->nullable();
            $table->double('pending')->nullable();
            $table->double('prelp_views')->nullable();
            $table->double('prelp_clicks')->nullable();
            $table->double('profit')->nullable();
            $table->double('pubrevenue')->nullable();
            $table->double('revenue')->nullable();
            $table->double('revenuetype1')->nullable();
            $table->double('revenuetype2')->nullable();
            $table->double('revenuetype3')->nullable();
            $table->double('revenuetype4')->nullable();
            $table->double('revenuetype5')->nullable();
            $table->double('revenuetype6')->nullable();
            $table->double('revenuetype7')->nullable();
            $table->double('revenuetype8')->nullable();
            $table->double('revenuetype9')->nullable();
            $table->double('revenuetype10')->nullable();
            $table->double('roi')->nullable();
            $table->string('sub1')->nullable();
            $table->string('sub2')->nullable();
            $table->string('sub3')->nullable();
            $table->string('sub4')->nullable();
            $table->string('sub5')->nullable();
            $table->string('sub6')->nullable();
            $table->string('sub7')->nullable();
            $table->double('total_conversions')->nullable();
            $table->double('total_revenue')->nullable();
            $table->double('tr')->nullable();
            $table->double('transactions')->nullable();
            $table->double('unique_clicks')->nullable();
            $table->date('date')->nullable();
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
        Schema::dropIfExists('reports');
    }
}
