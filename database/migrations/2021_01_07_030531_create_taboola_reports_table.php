<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaboolaReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taboola_reports', function (Blueprint $table) {
            $table->id();
            $table->integer('campaign_id')->unsigned();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('date')->nullable();
            $table->date('date_end_period')->nullable();
            $table->double('clicks')->nullable();
            $table->double('impressions')->nullable();
            $table->double('visible_impressions')->nullable();
            $table->double('spent')->nullable();
            $table->double('conversions_value')->nullable();
            $table->double('roas')->nullable();
            $table->double('ctr')->nullable();
            $table->double('vctr')->nullable();
            $table->double('cpm')->nullable();
            $table->double('vcpm')->nullable();
            $table->double('cpc')->nullable();
            $table->double('campaigns_num')->nullable();
            $table->double('cpa')->nullable();
            $table->double('cpa_clicks')->nullable();
            $table->double('cpa_views')->nullable();
            $table->double('cpa_actions_num')->nullable();
            $table->double('cpa_actions_num_from_clicks')->nullable();
            $table->double('cpa_actions_num_from_views')->nullable();
            $table->double('cpa_conversion_rate')->nullable();
            $table->double('cpa_conversion_rate_clicks')->nullable();
            $table->double('cpa_conversion_rate_views')->nullable();
            $table->string('currency')->nullable();
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
        Schema::dropIfExists('taboola_reports');
    }
}
