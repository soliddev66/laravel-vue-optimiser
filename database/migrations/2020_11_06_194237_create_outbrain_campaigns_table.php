<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOutbrainCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('outbrain_campaigns', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->unsigned();
            $table->integer('provider_id')->unsigned();
            $table->string('open_id')->nullable();
            $table->string('campaign_id')->nullable();
            $table->string('name')->nullable();
            $table->string('marketer_id')->nullable();
            $table->string('readonly')->nullable();
            $table->boolean('campaign_on_air')->nullable();
            $table->boolean('enabled')->nullable();
            $table->double('cpc')->nullable();
            $table->double('minimum_cpc')->nullable();
            $table->string('currency')->nullable();
            $table->boolean('auto_archived')->nullable();
            $table->longText('targeting')->nullable();
            $table->longText('budget')->nullable();
            $table->longText('feeds')->nullable();
            $table->double('auto_expiration_of_promoted_links')->nullable();
            $table->double('amount_spent')->nullable();
            $table->string('content_type')->nullable();
            $table->longText('suffix_tracking_code')->nullable();
            $table->longText('prefix_tracking_code')->nullable();
            $table->timestamp('last_modified')->nullable();
            $table->time('creation_time')->nullable();
            $table->longText('live_status')->nullable();
            $table->boolean('cpc_per_ad_enabled')->nullable();
            $table->longText('blocked_sites')->nullable();
            $table->string('start_hour')->nullable();
            $table->longText('tracking_pixels')->nullable();
            $table->longText('bids')->nullable();
            $table->longText('campaign_optimization')->nullable();
            $table->string('on_air_type')->nullable();
            $table->longText('on_air_reason')->nullable();
            $table->longText('scheduling')->nullable();
            $table->longText('objective')->nullable();
            $table->string('creative_format')->nullable();
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
        Schema::dropIfExists('outbrain_campaigns');
    }
}
