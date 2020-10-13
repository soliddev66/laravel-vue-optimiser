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
            $table->float('approved')->nullable();
            $table->float('attribution')->nullable();
            $table->float('baddevice')->nullable();
            $table->float('blacklist')->nullable();
            $table->float('clicks')->nullable();
            $table->float('conversions')->nullable();
            $table->float('convtype1')->nullable();
            $table->float('convtype2')->nullable();
            $table->float('convtype3')->nullable();
            $table->float('convtype4')->nullable();
            $table->float('convtype5')->nullable();
            $table->float('convtype6')->nullable();
            $table->float('convtype7')->nullable();
            $table->float('convtype8')->nullable();
            $table->float('convtype9')->nullable();
            $table->float('convtype10')->nullable();
            $table->float('cost')->nullable();
            $table->float('cpa')->nullable();
            $table->float('cpc')->nullable();
            $table->float('cpt')->nullable();
            $table->float('cr')->nullable();
            $table->float('ctr')->nullable();
            $table->float('datacenter')->nullable();
            $table->float('declined')->nullable();
            $table->float('epc')->nullable();
            $table->float('hour_of_day')->nullable();
            $table->float('impressions')->nullable();
            $table->float('impressions_visible')->nullable();
            $table->float('lp_clicks')->nullable();
            $table->float('lp_ctr')->nullable();
            $table->float('lp_views')->nullable();
            $table->float('ok')->nullable();
            $table->float('other')->nullable();
            $table->float('pending')->nullable();
            $table->float('prelp_clicks')->nullable();
            $table->float('profit')->nullable();
            $table->float('pubrevenue')->nullable();
            $table->float('revenue')->nullable();
            $table->float('revenuetype1')->nullable();
            $table->float('revenuetype2')->nullable();
            $table->float('revenuetype3')->nullable();
            $table->float('revenuetype4')->nullable();
            $table->float('revenuetype5')->nullable();
            $table->float('revenuetype6')->nullable();
            $table->float('revenuetype7')->nullable();
            $table->float('revenuetype8')->nullable();
            $table->float('revenuetype9')->nullable();
            $table->float('revenuetype10')->nullable();
            $table->float('roi')->nullable();
            $table->string('sub1')->nullable();
            $table->string('sub2')->nullable();
            $table->string('sub3')->nullable();
            $table->string('sub4')->nullable();
            $table->string('sub5')->nullable();
            $table->string('sub6')->nullable();
            $table->string('sub7')->nullable();
            $table->float('total_conversions')->nullable();
            $table->float('total_revenue')->nullable();
            $table->float('tr')->nullable();
            $table->float('transactions')->nullable();
            $table->float('unique_clicks')->nullable();
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
