<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <label class="p-2" :class="{ 'bg-primary': currentStep === 1 }">Campaign {{ actionName }}</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">Add Card</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Add Tweet</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 4 }">Preview</label>
          </div>
          <div class="card-body">
            <form class="row" v-if="selectedProvider && selectedAccount">
              <div class="col" v-if="currentStep == 1">
                <h2>General information</h2>
                <div class="form-group row">
                  <label for="advertiser" class="col-sm-2 control-label mt-2">Advertiser</label>
                  <div class="col-sm-4" v-if="advertisers.length">
                    <select name="advertiser" class="form-control" v-model="selectedAdvertiser" :disabled="instance" @change="selectedAdvertiserChange">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in advertisers" :key="advertiser.id">{{ advertiser.id }} - {{ advertiser.name }}</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row" v-if="selectedAdvertiser">
                  <label for="funding_instrument" class="col-sm-2 control-label mt-2">Funding Instrument</label>
                  <div class="col-lg-10 col-xl-8" v-if="fundingInstruments.length">
                    <select name="funding_instrument" class="form-control" v-model="selectedFundingInstrument" :disabled="instance">
                      <option value="">Select Funding Instrument</option>
                      <option :value="fundingInstrument.id" v-for="fundingInstrument in fundingInstruments" :key="fundingInstrument.id">{{ fundingInstrument.id }} - {{ fundingInstrument.name }}</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="entity_status" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'ACTIVE' }">
                        <input type="radio" name="entity_status" id="campaignStatus1" autocomplete="off" value="ACTIVE" v-model="campaignStatus">ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'DRAFT' }">
                        <input type="radio" name="entity_status" id="campaignStatus2" autocomplete="off" value="DRAFT" v-model="campaignStatus">DRAFT
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'PAUSED' }">
                        <input type="radio" name="entity_status" id="campaignStatus3" autocomplete="off" value="PAUSED" v-model="campaignStatus">PAUSED
                      </label>
                    </div>
                  </div>
                </div>
                <h2>Campaign Setting</h2>
                <div class="form-group row">
                  <label for="start_time" class="col-sm-2 control-label mt-2">Start Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="date" name="start_time" class="form-control" v-model="campaignStartTime" />
                  </div>
                  <label for="end_time" class="col-sm-2 control-label mt-2">End Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="date" name="end_time" class="form-control" v-model="campaignEndTime" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="daily_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Daily Budget Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="daily_budget_amount_local_micro" min="0" class="form-control" v-model="campaignDailyBudgetAmountLocalMicro" />
                  </div>
                  <label for="total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="total_budget_amount_local_micro" min="0" class="form-control" v-model="campaignTotalBudgetAmountLocalMicro" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="duration_in_days" class="col-sm-2 control-label mt-2">Duration In Days</label>
                  <div class="col-lg-10 col-xl-8">
                    <select name="duration_in_days" class="form-control" v-model="campaignDurationInDays">
                      <option value="">Select Duration</option>
                      <option value="1">1 day</option>
                      <option value="7">7 days</option>
                      <option value="30">30 days</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="frequency_cap" class="col-sm-2 control-label mt-2">Frequency Cap</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="number" name="frequency_cap" min="0" placeholder="Frequency Cap" class="form-control" v-model="campaignFrequencyCap" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="purchase_order_number" class="col-sm-2 control-label mt-2">Purchase Order Number</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="purchase_order_number" class="form-control" v-model="campaignPurchaseOrderNumber" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="standard_delivery" class="col-sm-2 control-label mt-2">Standard Delivery</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignStandardDelivery }">
                        <input type="radio" name="standard_delivery" id="campaignStandardDelivery1" autocomplete="off" :value="true" v-model="campaignStandardDelivery">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !campaignStandardDelivery }">
                        <input type="radio" name="standard_delivery" id="campaignStandardDelivery2" autocomplete="off" :value="false" v-model="campaignStandardDelivery">FALSE
                      </label>
                    </div>
                  </div>
                </div>
                <h2>Ad Group</h2>
                <div class="form-group row">
                  <label for="ad_group_name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="ad_group_name" placeholder="Name" class="form-control" v-model="adGroupName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_status" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'ACTIVE' }">
                        <input type="radio" name="ad_group_status" id="adGroupStatus1" autocomplete="off" value="ACTIVE" v-model="adGroupStatus">ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'DRAFT' }">
                        <input type="radio" name="ad_group_status" id="adGroupStatus2" autocomplete="off" value="DRAFT" v-model="adGroupStatus">DRAFT
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'PAUSED' }">
                        <input type="radio" name="ad_group_status" id="adGroupStatus3" autocomplete="off" value="PAUSED" v-model="adGroupStatus">PAUSED
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_start_time" class="col-sm-2 control-label mt-2">Start Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="date" name="start_time" class="form-control" v-model="adGroupStartTime" />
                  </div>
                  <label for="ad_group_end_time" class="col-sm-2 control-label mt-2">End Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="date" name="end_time" class="form-control" v-model="adGroupEndTime" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_android_app_store_identifier" class="col-sm-2 control-label mt-2">Android App Store Identifier</label>
                  <div class="col-lg-10 col-xl-8">
                    <select name="ad_group_android_app_store_identifier" class="form-control" v-model="adGroupAndroidAppStoreIdentifier">
                      <option value="">Select</option>
                      <option value="APP_ENGAGEMENTS">APP_ENGAGEMENTS</option>
                      <option value="APP_INSTALLS">APP_INSTALLS</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_ios_app_store_identifier" class="col-sm-2 control-label mt-2">iOS App Store Identifier</label>
                  <div class="col-lg-10 col-xl-8">
                    <select name="ad_group_ios_app_store_identifier" class="form-control" v-model="adGroupIOSAppStoreIdentifier">
                      <option value="">Select</option>
                      <option value="APP_ENGAGEMENTS">APP_ENGAGEMENTS</option>
                      <option value="APP_INSTALLS">APP_INSTALLS</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_objective" class="col-sm-2 control-label mt-2">Objective</label>
                  <div class="col-lg-10 col-xl-8">
                    <select name="ad_group_objective" class="form-control" v-model="adGroupObjective">
                      <option value="APP_ENGAGEMENTS">APP_ENGAGEMENTS</option>
                      <option value="APP_INSTALLS">APP_INSTALLS</option>
                      <option value="REACH">REACH</option>
                      <option value="FOLLOWERS">FOLLOWERS</option>
                      <option value="ENGAGEMENTS">ENGAGEMENTS</option>
                      <option value="VIDEO_VIEWS">VIDEO_VIEWS</option>
                      <option value="PREROLL_VIEWS">PREROLL_VIEWS</option>
                      <option value="WEBSITE_CLICKS">WEBSITE_CLICKS</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_placements" class="col-sm-2 control-label mt-2">Placements</label>
                  <div class="col-lg-10 col-xl-8">
                    <select2 id="ad_group_placements" name="ad_group_placements" :options="placements" v-model="adGroupPlacements" :settings="{ multiple: true }"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_product_type" class="col-sm-2 control-label mt-2">Product Type</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupProductType === 'MEDIA' }">
                        <input type="radio" name="ad_group_product_type" id="adGroupProductType1" autocomplete="off" value="MEDIA" v-model="adGroupProductType">MEDIA
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupProductType === 'PROMOTED_ACCOUNT' }">
                        <input type="radio" name="ad_group_product_type" id="adGroupProductType2" autocomplete="off" value="PROMOTED_ACCOUNT" v-model="adGroupProductType">PROMOTED_ACCOUNT
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupProductType === 'PROMOTED_TWEETS' }">
                        <input type="radio" name="ad_group_product_type" id="adGroupProductType3" autocomplete="off" value="PROMOTED_TWEETS" v-model="adGroupProductType">PROMOTED_TWEETS
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_advertiser_domain" class="col-sm-2 control-label mt-2">Advertiser Domain</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="ad_group_advertiser_domain" placeholder="Advertiser Domain" class="form-control" v-model="adGroupAdvertiserDomain" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_bid_amount_local_micro" class="col-sm-2 control-label mt-2">Bid Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="ad_group_bid_amount_local_micro" min="0" class="form-control" v-model="adGroupBidAmountLocalMicro" />
                  </div>
                  <label for="ad_group_total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="ad_group_total_budget_amount_local_micro" min="0" class="form-control" v-model="adGroupTotalBudgetAmountLocalMicro" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_categories" class="col-sm-2 control-label mt-2">Category</label>
                  <div class="col-lg-10 col-xl-8">
                    <select2 id="ad_group_categories" name="ad_group_categories" :options="adGroupCategorySelection" v-model="adGroupCategories" :settings="{ multiple: true }"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_primary_web_event_tag" class="col-sm-2 control-label mt-2">Primary Web Event Tag</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="ad_group_primary_web_event_tag" placeholder="Primary Web Event Tag" class="form-control" v-model="adGroupPrimaryWebEventTag" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_advertiser_user_id" class="col-sm-2 control-label mt-2">Advertiser User Id</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="number" name="ad_group_advertiser_user_id" min="0" class="form-control" v-model="adGroupAdvertiserUserId" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_automatically_select_bid" class="col-sm-2 control-label mt-2">Automatically Select Bid</label>
                  <div class="col-lg-4 col-xl-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupAutomaticallySelectBid }">
                        <input type="radio" name="ad_group_automatically_select_bid" id="adGroupAutomaticallySelectBid1" autocomplete="off" :value="true" v-model="adGroupAutomaticallySelectBid">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !adGroupAutomaticallySelectBid }">
                        <input type="radio" name="ad_group_automatically_select_bid" id="adGroupAutomaticallySelectBid2" autocomplete="off" :value="false" v-model="adGroupAutomaticallySelectBid">FALSE
                      </label>
                    </div>
                  </div>
                  <label for="ad_group_bid_type" class="col-sm-2 control-label mt-2">Bid Type</label>
                  <div class="col-lg-4 col-xl-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupBidType === 'AUTO' && !adGroupBidAmountLocalMicro }">
                        <input type="radio" name="ad_group_bid_type" id="adGroupBidType1" autocomplete="off" value="AUTO" v-model="adGroupBidType">AUTO
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupBidType === 'MAX' && !adGroupBidAmountLocalMicro }">
                        <input type="radio" name="ad_group_bid_type" id="adGroupBidType2" autocomplete="off" value="MAX" v-model="adGroupBidType">MAX
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupBidType === 'TARGET' && !adGroupBidAmountLocalMicro }">
                        <input type="radio" name="ad_group_bid_type" id="adGroupBidType3" autocomplete="off" value="TARGET" v-model="adGroupBidType">TARGET
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_bid_unit" class="col-sm-2 control-label mt-2">Bid Unit</label>
                  <div class="col-lg-4 col-xl-3">
                    <select name="ad_group_bid_unit" class="form-control" v-model="adGroupBidUnit">
                      <option value="">Select</option>
                      <option value="APP_CLICK">APP_CLICK</option>
                      <option value="APP_INSTALL">APP_INSTALL</option>
                      <option value="VIEW">VIEW</option>
                      <option value="VIEW_3S_100PCT">VIEW_3S_100PCT</option>
                      <option value="VIEW_6S">VIEW_6S</option>
                    </select>
                  </div>
                  <label for="ad_group_charge_by" class="col-sm-2 control-label mt-2">Charge By</label>
                  <div class="col-lg-4 col-xl-3">
                    <select name="ad_group_charge_by" class="form-control" v-model="adGroupChargeBy" disabled>
                      <option value="">Select</option>
                      <option value="APP_CLICK">APP_CLICK</option>
                      <option value="APP_INSTALL">APP_INSTALL</option>
                      <option value="VIEW">VIEW</option>
                      <option value="VIEW_3S_100PCT">VIEW_3S_100PCT</option>
                      <option value="VIEW_6S">VIEW_6S</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_optimization" class="col-sm-2 control-label mt-2">Optimization</label>
                  <div class="col-lg-10 col-xl-8">
                    <select name="ad_group_optimization" class="form-control" v-model="adGroupOptimization">
                      <option value="">Select</option>
                      <option value="APP_CLICKS">APP_CLICKS</option>
                      <option value="APP_INSTALLS">APP_INSTALLS</option>
                      <option value="DEFAULT">DEFAULT</option>
                      <option value="ENGAGEMENTS">ENGAGEMENTS</option>
                      <option value="WEBSITE_CONVERSIONS">WEBSITE_CONVERSIONS</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_audience_expansion" class="col-sm-2 control-label mt-2">Audience Expansion</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupAudienceExpansion === 'BROAD' }">
                        <input type="radio" name="ad_group_audience_expansion" id="adGroupAudienceExpansion1" autocomplete="off" value="BROAD" v-model="adGroupAudienceExpansion">BROAD
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupAudienceExpansion === 'DEFINED' }">
                        <input type="radio" name="ad_group_audience_expansion" id="adGroupAudienceExpansion2" autocomplete="off" value="DEFINED" v-model="adGroupAudienceExpansion">DEFINED
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupAudienceExpansion === 'EXPANDED' }">
                        <input type="radio" name="ad_group_audience_expansion" id="adGroupAudienceExpansion3" autocomplete="off" value="EXPANDED" v-model="adGroupAudienceExpansion">EXPANDED
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_tracking_tags" class="col-sm-2 control-label mt-2">Tracking Tags</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="ad_group_tracking_tags" placeholder="Tracking Tags" class="form-control" v-model="adGrouptrackingTags" />
                  </div>
                </div>
              </div>
              <!-- <div class="col" v-if="currentStep == 2">
                <h2>General information</h2>

                <div class="form-group row">
                  <label for="card_name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_name" placeholder="Enter a name" class="form-control" v-model="cardName" />
                  </div>
                </div>
                <h2>Component</h2>
                <div class="row">
                  <div class="col-10">
                    <fieldset class="mb-4 p-3 rounded border">
                      <fieldset class="mb-3 p-3 rounded border" v-for="(cardComponent, index) in cardComponents" :key="index">
                        <div class="form-group row">
                          <label for="card_component" class="col-sm-2 control-label mt-2">Component Type</label>
                          <div class="col-10">
                            <select name="card_component" class="form-control" v-model="cardComponent.type" @change="cardComponentTypeChange(index)">
                              <option value="">Select</option>
                              <option value="MEDIA">MEDIA</option>
                              <option value="SWIPEABLE_MEDIA">SWIPEABLE_MEDIA</option>
                              <option value="DETAILS">DETAILS</option>
                              <option value="BUTTON">BUTTON</option>
                            </select>
                          </div>
                        </div>
                        <div class="row" v-if="cardComponent.type == 'MEDIA'">
                          <div class="col">
                            <div class="form-group row">
                              <label for="card_component_MEDIA_media_key" class="col-sm-2 control-label mt-2">Media</label>
                              <div class="col-10">
                                <select2 id="card_component_MEDIA_media_key" name="card_component_MEDIA_media_key" :options="placements" v-model="cardComponent.media_key"></select2>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row" v-if="cardComponent.type == 'SWIPEABLE_MEDIA'">
                          <div class="col">
                            <div class="form-group row">
                              <label for="card_component_SWIPEABLE_MEDIA_media_keys" class="col-sm-2 control-label mt-2">Media</label>
                              <div class="col-10">
                                <select2 id="card_component_SWIPEABLE_MEDIA_media_keys" name="card_component_SWIPEABLE_MEDIA_media_keys" :options="placements" v-model="cardComponent.media_keys" :settings="{ multiple: true }"></select2>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row" v-if="cardComponent.type == 'DETAILS'">
                          <div class="col">
                            <div class="form-group row">
                              <label for="card_component_DETAILS_title" class="col-sm-2 control-label mt-2">Title</label>
                              <div class="col-10">
                                <input type="text" name="card_component_DETAILS_title" placeholder="Enter a title" class="form-control" v-model="cardComponent.title" />
                              </div>
                            </div>
                            <h3>Destination</h3>
                            <div class="form-group row">
                              <label for="card_component_DETAILS_destination_type" class="col-sm-2 control-label mt-2">Type</label>
                              <div class="col-10">
                                <select name="card_component" class="form-control" v-model="cardComponent.destination.type" @change="cardComponentDestinationTypeChange(index)">
                                  <option value="">Select</option>
                                  <option value="WEBSITE">WEBSITE</option>
                                  <option value="APP">APP</option>
                                </select>
                              </div>
                            </div>
                            <div class="row" v-if="cardComponent.destination.type == 'WEBSITE'">
                              <div class="col">
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_url" class="col-sm-2 control-label mt-2">Url</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_url" placeholder="Enter a url" class="form-control" v-model="cardComponent.destination.url" />
                                  </div>
                                </div>
                              </div>
                            </div>
                            <div class="row" v-if="cardComponent.destination.type == 'APP'">
                              <div class="col">
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_country_code" class="col-sm-2 control-label mt-2">Country</label>
                                  <div class="col-10">
                                    <select name="card_component" class="form-control" v-model="cardComponent.destination.country_code">
                                      <option value="">Select</option>
                                      <option value="US">US</option>
                                    </select>
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_ipad_app_id" class="col-sm-2 control-label mt-2">iPad App ID</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_destination_ipad_app_id" placeholder="iPad App ID" class="form-control" v-model="cardComponent.destination.ipad_app_id" />
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_iphone_app_id" class="col-sm-2 control-label mt-2">iPhone App ID</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_destination_iphone_app_id" placeholder="iPhone App ID" class="form-control" v-model="cardComponent.destination.iphone_app_id" />
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_googleplay_app_id" class="col-sm-2 control-label mt-2">Google Play App</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_destination_googleplay_app_id" placeholder="Google Play application package name" class="form-control" v-model="cardComponent.destination.googleplay_app_id" />
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_ipad_deep_link" class="col-sm-2 control-label mt-2">iPad Deep Link</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_destination_ipad_deep_link" placeholder="iPad Deep Link" class="form-control" v-model="cardComponent.destination.ipad_deep_link" />
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_iphone_deep_link" class="col-sm-2 control-label mt-2">iPhone Deep Link</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_destination_iphone_deep_link" placeholder="iPhone Deep Link" class="form-control" v-model="cardComponent.destination.iphone_deep_link" />
                                  </div>
                                </div>
                                <div class="form-group row">
                                  <label for="card_component_DETAILS_destination_googleplay_deep_link" class="col-sm-2 control-label mt-2">Android Deep Link</label>
                                  <div class="col-10">
                                    <input type="text" name="card_component_DETAILS_destination_googleplay_deep_link" placeholder="Android Deep Link" class="form-control" v-model="cardComponent.destination.googleplay_deep_link" />
                                  </div>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </fieldset>
                      <button class="btn btn-primary btn-sm" @click.prevent="addCardComponent()">Add New</button>
                    </fieldset>
                  </div>
                </div>
              </div> -->
              <div class="col" v-if="currentStep == 2">
                <h2>General information</h2>
                <div class="form-group row">
                  <label for="card_name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_name" placeholder="Enter a name" class="form-control" v-model="cardName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_media" class="col-sm-2 control-label mt-2">Media Image</label>
                  <div class="col-sm-8">
                    <input type="text" name="card_media" placeholder="Media Image" class="form-control" v-model="cardMedia" disabled />
                  </div>
                  <div class="col-sm-8 offset-sm-2">
                    <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('cardMedia')">Choose File</button>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_website_title" class="col-sm-2 control-label mt-2">Website Title</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_website_title" placeholder="Enter website title" class="form-control" v-model="cardWebsiteTitle" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_website_url" class="col-sm-2 control-label mt-2">Website URL</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_website_url" placeholder="Enter a website URL" class="form-control" v-model="cardWebsiteUrl" />
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body" v-if="currentStep == 3">
            <div class="form-group row">
              <label for="tweet_text" class="col-sm-2 control-label mt-2">Text</label>
              <div class="col-lg-10 col-xl-8">
                <input type="text" name="tweet_text" placeholder="Enter texts" class="form-control" v-model="tweetText" />
              </div>
            </div>
            <div class="form-group row">
              <label for="tweet_nullcast" class="col-sm-2 control-label mt-2">Nullcast</label>
              <div class="col-lg-4 col-xl-3">
                <div class="btn-group btn-group-toggle">
                  <label class="btn bg-olive" :class="{ active: tweetNullcast }">
                    <input type="radio" name="tweet_nullcast" id="tweetNullcast1" autocomplete="off" :value="true" v-model="tweetNullcast">TRUE
                  </label>
                  <label class="btn bg-olive" :class="{ active: !tweetNullcast }">
                    <input type="radio" name="tweet_nullcast" id="tweetNullcast2" autocomplete="off" :value="false" v-model="tweetNullcast">FALSE
                  </label>
                </div>
              </div>
              <label for="tweet_trim_user" class="col-sm-2 control-label mt-2">Trim User</label>
              <div class="col-lg-4 col-xl-3">
                <div class="btn-group btn-group-toggle">
                  <label class="btn bg-olive" :class="{ active: tweetTrimUser }">
                    <input type="radio" name="tweet_trim_user" id="tweetTrimUser1" autocomplete="off" :value="true" v-model="tweetTrimUser">TRUE
                  </label>
                  <label class="btn bg-olive" :class="{ active: !tweetTrimUser }">
                    <input type="radio" name="tweet_trim_user" id="tweetTrimUser2" autocomplete="off" :value="false" v-model="tweetTrimUser">FALSE
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label for="tweet_video_cta" class="col-sm-2 control-label mt-2">Video CTA</label>
              <div class="col-lg-4 col-xl-3">
                <select name="tweet_video_cta" class="form-control" v-model="tweetVideoCTA">
                  <option value="">Select</option>
                  <option value="VISIT_SITE">VISIT_SITE</option>
                  <option value="WATCH_NOW">WATCH_NOW</option>
                </select>
              </div>
              <label for="tweet_video_cta_value" class="col-sm-2 control-label mt-2">Video CTA Value</label>
              <div class="col-lg-4 col-xl-3">
                <input type="text" name="tweet_video_cta_value" placeholder="Video CTA Value" class="form-control" v-model="tweetVideoCTAValue" />
              </div>
            </div>
            <div class="form-group row">
              <label for="tweet_tweet_mode" class="col-sm-2 control-label mt-2">Tweet Mode</label>
              <div class="col-lg-10 col-xl-8">
                <div class="btn-group btn-group-toggle">
                  <label class="btn bg-olive" :class="{ active: tweetTweetMode == 'compat' }">
                    <input type="radio" name="tweet_tweet_mode" id="tweetTweetMode1" autocomplete="off" value="compat" v-model="tweetTweetMode">COMPAT
                  </label>
                  <label class="btn bg-olive" :class="{ active: tweetTweetMode == 'extended' }">
                    <input type="radio" name="tweet_trim_user" id="tweetTweetMode2" autocomplete="off" value="extended" v-model="tweetTweetMode">EXTENDED
                  </label>
                </div>
              </div>
            </div>
            <div class="form-group row">
              <label for="tweet_video_title" class="col-sm-2 control-label mt-2">Video Title</label>
              <div class="col-lg-10 col-xl-8">
                <input type="text" name="tweet_video_title" placeholder="Video Title" class="form-control" v-model="tweetVideoTitle" />
              </div>
            </div>
            <div class="form-group row">
              <label for="tweet_video_description" class="col-sm-2 control-label mt-2">Video Description</label>
              <div class="col-lg-10 col-xl-8">
                <textarea class="form-control" id="tweet_video_description" name="tweet_video_description" rows="3" v-model="tweetVideoDescription"></textarea>
              </div>
            </div>
          </div>
          <div class="card-body" v-if="currentStep == 4">
            <div class="row">
              <div class="col-sm-12">
                <h2>Preview</h2>
                <div v-html="previewData"></div>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="submitStep1State">Add Card</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="submitStep2State">Add Tweet</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 3">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep3" :disabled="submitStep3State">Submit</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 4">
              <button type="button" class="btn btn-primary">Finish</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal width="60%" height="80%" name="cardMedia">
      <file-manager v-bind:settings="settings" :props="{
          upload: true,
          viewType: 'grid',
          selectionType: 'single'
      }"></file-manager>
    </modal>
  </section>
</template>

<script>
import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    instance: {
      type: Object,
      default: null
    },
    action: {
      type: String,
      default: 'create'
    },
    selectedProvider: {
      type: String,
      default: null
    },
    selectedAccount: {
      type: String,
      default: null
    },
    step: {
      type: Number,
      default: 1
    }
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    submitStep1State() {
      return !this.selectedProvider || !this.selectedAccount || !this.selectedAdvertiser || !this.selectedFundingInstrument || !this.campaignName || !this.campaignStartTime || !this.campaignDailyBudgetAmountLocalMicro || !this.adGroupName || !this.adGroupStartTime
    },
    submitStep2State() {
      return !this.cardName || !this.cardWebsiteTitle || !this.cardWebsiteUrl || !this.cardMedia
    },
    submitStep3State() {
      return !this.tweetText
    }
  },
  mounted() {
    this.currentStep = this.step

    this.loadAdvertisers()

    let vm = this
    this.$root.$on('fm-selected-items', (value) => {
      const selectedFilePath = value[0].path
      if (this.openingFileSelector === 'cardMedia') {
        this.cardMedia = selectedFilePath
      }
      vm.$modal.hide(this.openingFileSelector)
    });
  },
  watch: {

  },
  data() {
    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      currentStep: 1,
      redtrackKey: '',
      languages: [],
      countries: [],
      advertisers: [],
      fundingInstruments: [],
      accounts: [],
      placements: [
        { id: 'ALL_ON_TWITTER', text: 'ALL_ON_TWITTER' },
        { id: 'PUBLISHER_NETWORK', text: 'PUBLISHER_NETWORK' },
        { id: 'TAP_BANNER', text: 'TAP_BANNER' },
        { id: 'TAP_FULL', text: 'TAP_FULL' },
        { id: 'TAP_FULL_LANDSCAPE', text: 'TAP_FULL_LANDSCAPE' },
        { id: 'TAP_NATIVE', text: 'TAP_NATIVE' },
        { id: 'TAP_MRECT', text: 'TAP_MRECT' },
        { id: 'TWITTER_PROFILE', text: 'TWITTER_PROFILE' },
        { id: 'TWITTER_SEARCH', text: 'TWITTER_SEARCH' },
        { id: 'TWITTER_TIMELINE', text: 'TWITTER_TIMELINE' }
      ],
      actionName: this.action,
      selectedAdvertiser: this.instance ? this.instance.advertiserId : '',
      selectedFundingInstrument: this.instance ? this.instance.fundingInstrument : '',
      campaignName: this.instance ? this.instance.campaignName : '',
      campaignStartTime: '',
      campaignEndTime: '',
      campaignDailyBudgetAmountLocalMicro: '',
      campaignTotalBudgetAmountLocalMicro: '',
      campaignDurationInDays: '',
      campaignFrequencyCap: '',
      campaignPurchaseOrderNumber: '',
      campaignStandardDelivery: true,
      campaignStatus: this.instance ? this.instance.channel : 'PAUSED',
      adGroupName: '',
      adGroupAndroidAppStoreIdentifier: '',
      adGroupIOSAppStoreIdentifier: '',
      adGroupPlacements: '',
      adGroupObjective: 'APP_ENGAGEMENTS',
      adGroupProductType: 'PROMOTED_TWEETS',
      adGroupAdvertiserDomain: '',
      adGroupBidAmountLocalMicro: '',
      adGroupTotalBudgetAmountLocalMicro: '',
      adGroupPrimaryWebEventTag: '',
      adGroupCategorySelection: null,
      adGroupCategories: [],
      adGroupAdvertiserUserId: '',
      adGroupAutomaticallySelectBid: false,
      adGroupBidType: '',
      adGroupStatus: 'PAUSED',
      adGroupBidUnit: '',
      adGroupChargeBy: '',
      adGroupStartTime: '',
      adGroupEndTime: '',
      adGroupOptimization: '',
      adGroupAudienceExpansion: '',
      adGrouptrackingTags: '',
      cardName: '',
      cardMedia: '',
      cardMediaImage: {
        size: '',
        height: '',
        width: ''
      },
      cardWebsiteTitle: '',
      cardWebsiteUrl: '',
      // cardComponents: [
      //   {
      //     type: '',
      //     destination: {}
      //   }
      // ],
      tweetText: '',
      tweetNullcast: true,
      tweetTrimUser: false,
      tweetVideoCTA: '',
      tweetTweetMode: '',
      tweetVideoCTAValue: '',
      tweetVideoTitle: '',
      tweetVideoDescription: '',
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      },
      openingFileSelector: '',
      previewData: '',
    }
  },
  methods: {
    selectedAdvertiserChange() {
      this.loadFundingInstruments();
      this.loadAdGroupCategories();
    },
    // cardComponentTypeChange(index) {
    //   switch (this.cardComponents[index].type) {
    //     case 'DETAILS':
    //       this.cardComponents[index].title = '';
    //       this.cardComponents[index].destination = {
    //         type: 'WEBSITE',
    //         url: ''
    //       }

    //       break;
    //   }
    // },
    // cardComponentDestinationTypeChange(index) {
    //   if (this.cardComponents[index].destination.type == 'APP') {
    //     this.cardComponents[index].destination = {
    //       type: 'APP',
    //       country_code: '',
    //       ipad_app_id: '',
    //       iphone_app_id: '',
    //       googleplay_app_id: '',
    //       ipad_deep_link: '',
    //       iphone_deep_link: '',
    //       googleplay_deep_link: ''
    //     }
    //   } else {
    //     this.cardComponents[index].destination = {
    //       type: 'WEBSITE',
    //       url: ''
    //     }
    //   }
    // },
    // addCardComponent() {
    //   this.cardComponents.push({
    //     type: '',
    //     destination: {}
    //   });
    // },
    openChooseFile(name) {
      this.openingFileSelector = name
      console.log(this.$modal)
      this.$modal.show(name)
    },
    loadAdvertisers() {
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.advertisers = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    loadFundingInstruments() {
      this.isLoading = true
      axios.get(`/account/funding-instruments?provider=${this.selectedProvider}&account=${this.selectedAccount}&advertiser=${this.selectedAdvertiser}`).then(response => {
        this.fundingInstruments = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    loadAdGroupCategories() {
      this.isLoading = true
      axios.get(`/account/ad-group-categories?provider=${this.selectedProvider}&account=${this.selectedAccount}&advertiser=${this.selectedAdvertiser}`).then(response => {
        this.adGroupCategorySelection = response.data.map(category => {
          return {
            id: category.id,
            text: category.name
          }
        })
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    submitStep1() {
      console.log(this.adGroupBidAmountLocalMicro)
      console.log(this.adGroupAutomaticallySelectBid)
      const step1Data = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        advertiser: this.selectedAdvertiser,
        fundingInstrument: this.selectedFundingInstrument,
        campaignName: this.campaignName,
        campaignStartTime: this.campaignStartTime,
        campaignEndTime: this.campaignEndTime,
        campaignDailyBudgetAmountLocalMicro: this.campaignDailyBudgetAmountLocalMicro,
        campaignTotalBudgetAmountLocalMicro: this.campaignTotalBudgetAmountLocalMicro,
        campaignDurationInDays: this.campaignDurationInDays,
        campaignStatus: this.campaignStatus,
        campaignFrequencyCap: this.campaignFrequencyCap,
        campaignPurchaseOrderNumber: this.campaignPurchaseOrderNumber,
        campaignStandardDelivery: this.campaignStandardDelivery,
        adGroupName: this.adGroupName,
        adGroupAndroidAppStoreIdentifier: this.adGroupAndroidAppStoreIdentifier,
        adGroupIOSAppStoreIdentifier: this.adGroupIOSAppStoreIdentifier,
        adGroupObjective: this.adGroupObjective,
        adGroupPlacements: this.adGroupPlacements,
        adGroupProductType: this.adGroupProductType,
        adGroupAdvertiserDomain: this.adGroupAdvertiserDomain,
        adGroupCategories: this.adGroupCategories,
        adGroupPrimaryWebEventTag: this.adGroupPrimaryWebEventTag,
        adGroupAdvertiserUserId: this.adGroupAdvertiserUserId,
        adGroupAutomaticallySelectBid: this.adGroupAutomaticallySelectBid,
        adGroupBidType: this.adGroupBidType,
        adGroupBidUnit: this.adGroupBidUnit,
        adGroupChargeBy: this.adGroupChargeBy,
        adGroupStartTime: this.adGroupStartTime,
        adGroupEndTime: this.adGroupEndTime,
        adGroupStatus: this.adGroupStatus,
        adGroupAudienceExpansion: this.adGroupAudienceExpansion,
        adGroupOptimization: this.adGroupOptimization,
        adGroupBidAmountLocalMicro: this.adGroupBidAmountLocalMicro,
        adGroupTotalBudgetAmountLocalMicro: this.adGroupTotalBudgetAmountLocalMicro,
        adGrouptrackingTags: this.adGrouptrackingTags
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {
        cardName: this.cardName,
        cardMedia: this.cardMedia,
        cardWebsiteTitle: this.cardWebsiteTitle,
        cardWebsiteUrl: this.cardWebsiteUrl
      }
      this.postData = {...this.postData, ...step2Data }

      this.currentStep = 3
    },
    submitStep3() {
      const step3Data = {
        tweetText: this.tweetText,
        tweetNullcast: this.tweetNullcast,
        tweetTrimUser: this.tweetTrimUser,
        tweetTweetMode: this.tweetTweetMode,
        tweetVideoCTA: this.tweetVideoCTA,
        tweetVideoCTAValue: this.tweetVideoCTAValue,
        tweetVideoTitle: this.tweetVideoTitle,
        tweetVideoDescription: this.tweetVideoDescription
      }

      this.postData = {...this.postData, ...step3Data }

      this.isLoading = true
      let url = '/campaigns';

      if (this.action == 'edit') {
        url += '/update/' + this.instance.instance_id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.currentStep = 4
          this.previewData = response.data.previewData
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = '/campaigns';
          });
        }
      }).catch(error => {
        console.log(error)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>
