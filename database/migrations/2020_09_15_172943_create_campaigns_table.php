<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('campaign_id')->nullable();
            $table->string('name')->nullable();
            $table->string('status')->nullable();
            $table->string('advertiser_id')->nullable();
            $table->string('language')->nullable();
            $table->string('tracking_url')->nullable();
            $table->string('objective')->nullable();
            $table->string('advanced_geo_pos')->nullable();
            $table->string('advanced_geo_neg')->nullable();
            $table->string('conversion_rule_ids')->nullable();
            $table->string('bidding_strategy')->nullable();
            $table->string('effective_status')->nullable();
            $table->string('budget_type')->nullable();
            $table->json('conversion_rule_config')->nullable();
            $table->float('budget')->nullable();
            $table->string('channel')->nullable();
            $table->string('is_partner_network')->nullable();
            $table->string('sub_channel_modifier')->nullable();
            $table->string('custom_parameters')->nullable();
            $table->string('sub_channel')->nullable();
            $table->string('editorial_status')->nullable();
            $table->string('is_deep_link')->nullable();
            $table->string('created_date')->nullable();
            $table->string('last_update_date')->nullable();
            $table->integer('provider_id')->nullable();
            $table->string('open_id')->nullable();
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
        Schema::dropIfExists('campaigns');
    }
}
