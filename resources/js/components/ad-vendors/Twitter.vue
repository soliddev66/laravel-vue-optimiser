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
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">Add Contents</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Generate Variations</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 4 }">Preview</label>
          </div>

          <div class="card-body">
            <form class="form-horizontal" v-if="selectedProvider && selectedAccount">
              <div v-if="currentStep == 1">
                <h2>General information</h2>

                <div class="form-group row">
                  <label for="advertiser" class="col-sm-2 control-label mt-2">Advertiser</label>
                  <div class="col-sm-4" v-if="advertisers.length">
                    <select name="advertiser" class="form-control" v-model="selectedAdvertiser" :disabled="instance" @change="selectedAdvertiserChange">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in advertisers" :key="advertiser.id">{{ advertiser.id }} - {{ advertiser.name }}</option>
                    </select>
                  </div>
                  <div class="col-sm-2" v-if="!instance">
                    <button type="button" class="btn btn-primary" @click.prevent="signUp()">Create New</button>
                  </div>
                </div>

                <div class="form-group row" v-if="selectedAdvertiser">
                  <label for="funding_instrument" class="col-sm-2 control-label mt-2">Funding Instrument</label>
                  <div class="col-sm-8" v-if="fundingInstruments.length">
                    <select name="funding_instrument" class="form-control" v-model="selectedFundingInstrument" :disabled="instance">
                      <option value="">Select Funding Instrument</option>
                      <option :value="fundingInstrument.id" v-for="fundingInstrument in fundingInstruments" :key="fundingInstrument.id">{{ fundingInstrument.id }} - {{ fundingInstrument.name }}</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-sm-8">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="type" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'ACTIVE' }">
                        <input type="radio" name="type" id="campaignStatus1" autocomplete="off" value="ACTIVE" v-model="campaignStatus">ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'DRAFT' }">
                        <input type="radio" name="type" id="campaignStatus2" autocomplete="off" value="DRAFT" v-model="campaignStatus">DRAFT
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'PAUSED' }">
                        <input type="radio" name="type" id="campaignStatus3" autocomplete="off" value="PAUSED" v-model="campaignStatus">PAUSED
                      </label>
                    </div>
                  </div>
                </div>

                <h2>Campaign Setting</h2>
                <div class="form-group row">
                  <label for="start_time" class="col-sm-2 control-label mt-2">Start Time</label>
                  <div class="col-sm-3">
                    <input type="date" name="start_time" class="form-control" v-model="campaignStartTime" />
                  </div>
                  <label for="end_time" class="col-sm-2 control-label mt-2">End Time</label>
                  <div class="col-sm-3">
                    <input type="date" name="end_time" class="form-control" v-model="campaignEndTime" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="daily_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Daily Budget Amount Local Micro</label>
                  <div class="col-sm-3">
                    <input type="number" name="daily_budget_amount_local_micro" min="0" class="form-control" v-model="campaignDailyBudgetAmountLocalMicro" />
                  </div>
                  <label for="total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
                  <div class="col-sm-3">
                    <input type="number" name="total_budget_amount_local_micro" min="0" class="form-control" v-model="campaignTotalBudgetAmountLocalMicro" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="duration_in_days" class="col-sm-2 control-label mt-2">Duration In Days</label>
                  <div class="col-sm-8">
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
                  <div class="col-sm-8">
                    <input type="number" name="frequency_cap" min="0" class="form-control" v-model="campaignFrequencyCap" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="purchase_order_number" class="col-sm-2 control-label mt-2">Purchase Order Number</label>
                  <div class="col-sm-8">
                    <input type="text" name="purchase_order_number" class="form-control" v-model="campaignPurchaseOrderNumber" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="standard_delivery" class="col-sm-2 control-label mt-2">Standard Delivery</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignStandardDelivery }">
                        <input type="radio" name="standard_delivery" id="campaignStandardDelivery1" autocomplete="off" value="true" v-model="campaignStandardDelivery">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !campaignStandardDelivery }">
                        <input type="radio" name="standard_delivery" id="campaignStandardDelivery2" autocomplete="off" value="false" v-model="campaignStandardDelivery">FALSE
                      </label>
                    </div>
                  </div>
                </div>

                <h2>Ad Group</h2>

                <div class="form-group row">
                  <label for="ad_group_name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-sm-8">
                    <input type="text" name="ad_group_name" placeholder="Name" class="form-control" v-model="adGroupName" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="type" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'ACTIVE' }">
                        <input type="radio" name="type" id="adGroupStatus1" autocomplete="off" value="ACTIVE" v-model="adGroupStatus">ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'DRAFT' }">
                        <input type="radio" name="type" id="adGroupStatus2" autocomplete="off" value="DRAFT" v-model="adGroupStatus">DRAFT
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'PAUSED' }">
                        <input type="radio" name="type" id="adGroupStatus3" autocomplete="off" value="PAUSED" v-model="adGroupStatus">PAUSED
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="objective" class="col-sm-2 control-label mt-2">Android App Store Identifier</label>
                  <div class="col-sm-8">
                    <select name="objective" class="form-control" v-model="adGroupAndroidAppStoreIdentifier">
                      <option value="">Select</option>
                      <option value="APP_ENGAGEMENTS">APP_ENGAGEMENTS</option>
                      <option value="APP_INSTALLS">APP_INSTALLS</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="objective" class="col-sm-2 control-label mt-2">iOS App Store Identifier</label>
                  <div class="col-sm-8">
                    <select name="objective" class="form-control" v-model="adGroupIOSAppStoreIdentifier">
                      <option value="">Select</option>
                      <option value="APP_ENGAGEMENTS">APP_ENGAGEMENTS</option>
                      <option value="APP_INSTALLS">APP_INSTALLS</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="objective" class="col-sm-2 control-label mt-2">Objective</label>
                  <div class="col-sm-8">
                    <select name="objective" class="form-control" v-model="adGroupObjective">
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
                  <label for="placements" class="col-sm-2 control-label mt-2">Placements</label>
                  <div class="col-sm-8">
                    <select2 id="placements" name="placements" :options="placements" v-model="adGroupPlacements" :settings="{ multiple: true }"></select2>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="product_type" class="col-sm-2 control-label mt-2">Product Type</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupProductType === 'MEDIA' }">
                        <input type="radio" name="product_type" id="adGroupProductType1" autocomplete="off" value="MEDIA" v-model="adGroupProductType">MEDIA
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupProductType === 'PROMOTED_ACCOUNT' }">
                        <input type="radio" name="product_type" id="adGroupProductType2" autocomplete="off" value="PROMOTED_ACCOUNT" v-model="adGroupProductType">PROMOTED_ACCOUNT
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupProductType === 'PROMOTED_TWEETS' }">
                        <input type="radio" name="product_type" id="adGroupProductType2" autocomplete="off" value="PROMOTED_TWEETS" v-model="adGroupProductType">PROMOTED_TWEETS
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="advertiser_domain" class="col-sm-2 control-label mt-2">Advertiser Domain</label>
                  <div class="col-sm-8">
                    <input type="text" name="advertiser_domain" placeholder="Advertiser Domain" class="form-control" v-model="adGroupAdvertiserDomain" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="daily_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Bid Amount Local Micro</label>
                  <div class="col-sm-3">
                    <input type="number" name="daily_budget_amount_local_micro" min="0" class="form-control" v-model="adGroupBidAmountLocalMicro" />
                  </div>
                  <label for="ad_group_total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
                  <div class="col-sm-3">
                    <input type="number" name="ad_group_total_budget_amount_local_micro" min="0" class="form-control" v-model="adGroupTotalBudgetAmountLocalMicro" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="category" class="col-sm-2 control-label mt-2">Category</label>
                  <div class="col-sm-8">
                    <input type="text" name="category" placeholder="Category" class="form-control" v-model="adGroupCategory" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="primary_web_event_tag" class="col-sm-2 control-label mt-2">Primary Web Event Tag</label>
                  <div class="col-sm-8">
                    <input type="text" name="primary_web_event_tag" placeholder="Primary Web Event Tag" class="form-control" v-model="adGroupPrimaryWebEventTag" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="advertiser_user_id" class="col-sm-2 control-label mt-2">Advertiser User Id</label>
                  <div class="col-sm-8">
                    <input type="number" name="advertiser_user_id" min="0" class="form-control" v-model="adGroupAdvertiserUserId" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="automatically_select_bid" class="col-sm-2 control-label mt-2">Automatically Select Bid</label>
                  <div class="col-sm-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupAutomaticallySelectBid }">
                        <input type="radio" name="automatically_select_bid" id="adGroupAutomaticallySelectBid1" autocomplete="off" value="true" v-model="adGroupAutomaticallySelectBid">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !adGroupAutomaticallySelectBid }">
                        <input type="radio" name="automatically_select_bid" id="adGroupAutomaticallySelectBid2" autocomplete="off" value="false" v-model="adGroupAutomaticallySelectBid">FALSE
                      </label>
                    </div>
                  </div>

                  <label for="bid_type" class="col-sm-2 control-label mt-2">Bid Type</label>
                  <div class="col-sm-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupBidType }">
                        <input type="radio" name="bid_type" id="adGroupBidType1" autocomplete="off" value="AUTO" v-model="adGroupBidType">AUTO
                      </label>
                      <label class="btn bg-olive" :class="{ active: !adGroupBidType }">
                        <input type="radio" name="bid_type" id="adGroupBidType2" autocomplete="off" value="MAX" v-model="adGroupBidType">MAX
                      </label>
                      <label class="btn bg-olive" :class="{ active: !adGroupBidType }">
                        <input type="radio" name="bid_type" id="adGroupBidType2" autocomplete="off" value="TARGET" v-model="adGroupBidType">TARGET
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                </div>

                <div class="form-group row">
                  <label for="bid_unit" class="col-sm-2 control-label mt-2">Bid Unit</label>
                  <div class="col-sm-3">
                    <select name="bid_unit" class="form-control" v-model="adGroupBidUnit">
                      <option value="">Select</option>
                      <option value="APP_CLICK">APP_CLICK</option>
                      <option value="APP_INSTALL">APP_INSTALL</option>
                      <option value="VIEW">VIEW</option>
                      <option value="VIEW_3S_100PCT">VIEW_3S_100PCT</option>
                      <option value="VIEW_6S">VIEW_6S</option>
                    </select>
                  </div>
                  <label for="charge_by" class="col-sm-2 control-label mt-2">Charge By</label>
                  <div class="col-sm-3">
                    <select name="charge_by" class="form-control" v-model="adGroupChargeBy" disabled>
                      <option value="">Select</option>
                      <option value="APP_CLICK">APP_CLICK</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="optimization" class="col-sm-2 control-label mt-2">Optimization</label>
                  <div class="col-sm-8">
                    <select name="optimization" class="form-control" v-model="adGroupOptimization">
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
                  <label for="audience_expansion" class="col-sm-2 control-label mt-2">Audience Expansion</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupAudienceExpansion === 'BROAD' }">
                        <input type="radio" name="audience_expansion" id="adGroupAudienceExpansion1" autocomplete="off" value="BROAD" v-model="adGroupAudienceExpansion">BROAD
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupAudienceExpansion === 'DEFINED' }">
                        <input type="radio" name="audience_expansion" id="adGroupAudienceExpansion2" autocomplete="off" value="DEFINED" v-model="adGroupAudienceExpansion">DEFINED
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupAudienceExpansion === 'EXPANDED' }">
                        <input type="radio" name="audience_expansion" id="adGroupAudienceExpansion3" autocomplete="off" value="EXPANDED" v-model="adGroupAudienceExpansion">EXPANDED
                      </label>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="tracking_tags" class="col-sm-2 control-label mt-2">Primary Web Event Tag</label>
                  <div class="col-sm-8">
                    <input type="text" name="tracking_tags" placeholder="Primary Web Event Tag" class="form-control" v-model="adGrouptrackingTags" />
                  </div>
                </div>
              </div>

              <div class="card-body" v-if="currentStep == 2">
                <div class="row">
                  <div class="col-sm-6">
                    <div class="form-group row">
                      <label for="title" class="col-sm-4 control-label mt-2">Title</label>
                      <div class="col-sm-8">
                        <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name</label>
                      <div class="col-sm-8">
                        <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="brandname" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="description" class="col-sm-4 control-label mt-2">Description</label>
                      <div class="col-sm-8">
                        <textarea class="form-control" rows="3" placeholder="Enter description" v-model="description"></textarea>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                      <div class="col-sm-8 text-center">
                        <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="displayUrl" />
                        <small class="text-danger" v-if="displayUrl && !displayUrlState">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                      <div class="col-sm-8 text-center">
                        <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="targetUrl" />
                        <small class="text-danger" v-if="targetUrl && !targetUrlState">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="image_hq_url" class="col-sm-4 control-label mt-2">Image HQ URL</label>
                      <div class="col-sm-8">
                        <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="imageUrlHQ" />
                      </div>
                      <div class="col-sm-8 offset-sm-4">
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('hqModal')">Choose File</button>
                        <!-- <input type="file" ref="imageHQ" @change="selectedHQFile" accept="image/*"> -->
                      </div>
                      <div class="col-sm-8 offset-sm-4 text-center">
                        <small class="text-danger" v-if="imageUrlHQ && !imageUrlHQState">URL is invalid. You might need http/https at the beginning.</small>
                        <small class="text-danger" v-if="imageHQ.size && !imageHQState">Image is invalid. You might need an 1200x627 image.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="image_url" class="col-sm-4 control-label mt-2">Image URL</label>
                      <div class="col-sm-8">
                        <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="imageUrl" />
                      </div>
                      <div class="col-sm-8 offset-sm-4">
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal')">Choose File</button>
                        <!-- <input type="file" ref="image" @change="selectedFile" accept="image/*"> -->
                      </div>
                      <div class="col-sm-8 offset-sm-4 text-center">
                        <small class="text-danger" v-if="imageUrl && !imageUrlState">URL is invalid. You might need http/https at the beginning.</small>
                        <small class="text-danger" v-if="image.size && !imageState">Image is invalid. You might need an 627x627 image.</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <h1>Preview</h1>
                    <div v-html="previewData"></div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body" v-if="currentStep == 3">
            <div class="row mb-2" v-for="(attribute, index) in attributes" :key="attribute.id">
              <div class="col-sm-12" v-if="index === 0">
                <h4>Main Variation</h4>
              </div>
              <div class="col-sm-12">
                <div class="form-group row">
                  <label :for="`gender${index}`" class="col-sm-4 control-label mt-2">Gender</label>
                  <div class="col-sm-8">
                    <select :name="`gender${index}`" class="form-control" v-model="attribute.gender">
                      <option value="">All</option>
                      <option value="MALE">Male</option>
                      <option value="FEMALE">Female</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group row">
                  <label :for="`age${index}`" class="col-sm-4 control-label mt-2">Age</label>
                  <div class="col-sm-8">
                    <select :name="`age${index}`" class="form-control" v-model="attribute.age" multiple>
                      <option value="">All</option>
                      <option value="18-24">18-24</option>
                      <option value="25-34">25-34</option>
                      <option value="35-44">35-44</option>
                      <option value="45-54">45-54</option>
                      <option value="55-64">55-64</option>
                      <option value="65-120">65-120</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-sm-12 border-bottom">
                <div class="form-group row">
                  <label :for="`device${index}`" class="col-sm-4 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select :name="`device${index}`" class="form-control" v-model="attribute.device">
                      <option value="">All</option>
                      <option value="SMARTPHONE">SMARTPHONE</option>
                      <option value="TABLET">TABLET</option>
                      <option value="DESKTOP">DESKTOP</option>
                    </select>
                  </div>
                </div>
              </div>
              <div class="col-sm-12 text-right mt-3">
                <button class="btn btn-warning btn-sm" @click="removeAttibute(index)" v-if="index > 0">Remove</button>
              </div>
            </div>
            <button class="btn btn-primary btn-sm" @click="addNewAttibute()">Add New</button>
          </div>
          <div class="card-body" v-if="currentStep == 4">
            <div class="col-sm-12 text-center">
              <div v-html="previewData"></div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="!campaignNameState || !selectedAdvertiserState || !campaignBudgetState || !adGroupNameState || !bidAmountState">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="!titleState || !brandnameState || !descriptionState || !displayUrlState || !targetUrlState || !imageUrlHQState || !imageUrlState">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 3">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep3">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 4">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep4">Finish</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal width="60%" height="80%" name="hqModal">
      <file-manager v-bind:settings="settings" :props="{
          upload: true,
          viewType: 'grid',
          selectionType: 'single'
      }"></file-manager>
    </modal>
    <modal width="60%" height="80%" name="imageModal">
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
    selectedAdvertiserState() {
      return this.selectedAdvertiser !== ''
    },
    campaignNameState() {
      return this.campaignName !== ''
    },
    campaignBudgetState() {
      return this.campaignBudget > 0
    },
    adGroupNameState() {
      return this.adGroupName !== ''
    },
    bidAmountState() {
      return this.bidAmount > 0
    },
    titleState() {
      return this.title !== ''
    },
    brandnameState() {
      return this.brandname !== ''
    },
    descriptionState() {
      return this.description !== ''
    },
    displayUrlState() {
      return this.displayUrl !== '' && this.validURL(this.displayUrl)
    },
    targetUrlState() {
      return this.targetUrl !== '' && this.validURL(this.targetUrl)
    },
    imageUrlHQState() {
      return (this.imageUrlHQ !== '' && this.validURL(this.imageUrlHQ)) || this.imageHQState
    },
    imageHQState() {
      return this.imageHQ.size !== '' && this.validSize(this.imageHQ, 'HQ')
    },
    imageUrlState() {
      return (this.imageUrl !== '' && this.validURL(this.imageUrl)) || this.imageState
    },
    imageState() {
      return this.image.size !== '' && this.validSize(this.image, '')
    }
  },
  mounted() {
    console.log('Component mounted.')
    let vm = this
    this.$root.$on('fm-selected-items', (value) => {
      const selectedFilePath = value[0].path
      if (this.openingFileSelector === 'hqModal') {
        this.imageUrlHQ = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
      }
      if (this.openingFileSelector === 'imageModal') {
        this.imageUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
      }
      vm.$modal.hide(this.openingFileSelector)
    });
    this.currentStep = this.step

    this.getLanguages()
    this.getCountries()
    this.loadAdvertisers()

    if (this.instance) {
      this.loadPreview()
    }
  },
  watch: {
    title: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    displayUrl: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    targetUrl: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    description: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    brandname: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    imageUrlHQ: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    imageUrl: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000)
  },
  data() {
    let campaignGender = '',
      campaignAge = [],
      campaignDevice = '',
      adGroupName = '',
      bidAmount = '0.05',
      campaignLocation = [],
      adGroupID = '',
      dataAttributes = [];
    if (this.instance) {
      this.instance.attributes.forEach(attribute => {
        if (attribute.type === 'GENDER') {
          campaignGender = attribute.value;
        } else if (attribute.type === 'AGE') {
          campaignAge.push(attribute.value);
        } else if (attribute.type === 'DEVICE') {
          campaignDevice = attribute.value;
        } else if (attribute.type === 'WOEID') {
          campaignLocation.push(attribute.value);
        }
        dataAttributes.push(attribute.id);
      });

      if (this.instance.adGroups.length > 0) {
        adGroupID = this.instance.adGroups[0]['id'];
        adGroupName = this.instance.adGroups[0]['adGroupName'];

        if (this.instance.adGroups[0]['bidSet']['bids'].length > 0) {
          bidAmount = this.instance.adGroups[0]['bidSet']['bids'][0]['value'];
        }
      }
    }

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
        {id: 'ALL_ON_TWITTER', text: 'ALL_ON_TWITTER'},
        {id: 'PUBLISHER_NETWORK', text: 'PUBLISHER_NETWORK'},
        {id: 'TAP_BANNER', text: 'TAP_BANNER'},
        {id: 'TAP_FULL', text: 'TAP_FULL'},
        {id: 'TAP_FULL_LANDSCAPE', text: 'TAP_FULL_LANDSCAPE'},
        {id: 'TAP_NATIVE', text: 'TAP_NATIVE'},
        {id: 'TAP_MRECT', text: 'TAP_MRECT'},
        {id: 'TWITTER_PROFILE', text: 'TWITTER_PROFILE'},
        {id: 'TWITTER_SEARCH', text: 'TWITTER_SEARCH'},
        {id: 'TWITTER_TIMELINE', text: 'TWITTER_TIMELINE'}
      ],
      actionName: this.action,
      selectedAdvertiser: this.instance ? this.instance.advertiserId : '',
      selectedFundingInstrument: this.instance ? this.instance.fundingInstrument : '',
      campaignName: this.instance ? this.instance.campaignName : '',
      campaignStatus: this.instance ? this.instance.channel : 'ACTIVE',
      adGroupAndroidAppStoreIdentifier: '',
      adGroupIOSAppStoreIdentifier: '',
      adGroupPlacements: '',
      adGroupObjective: 'APP_ENGAGEMENTS',
      adGroupProductType: 'PROMOTED_TWEETS',
      adGroupAdvertiserDomain: '',
      adGroupBidAmountLocalMicro: '',
      adGroupTotalBudgetAmountLocalMicro: '',
      adGroupPrimaryWebEventTag: '',
      adGroupCategory: '',
      adGroupAdvertiserUserId: '',
      adGroupAutomaticallySelectBid: false,
      adGroupBidType: 'AUTO',
      adGroupStatus: 'ACTIVE',
      adGroupBidUnit: '',
      adGroupChargeBy: '',
      adGroupOptimization: '',
      adGroupAudienceExpansion: '',
      adGrouptrackingTags: '',
      campaignLanguage: this.instance ? this.instance.language : 'en',
      campaignLocation: campaignLocation,
      campaignGender: campaignGender,
      campaignAge: campaignAge,
      campaignDevice: campaignDevice,
      campaignBudget: this.instance ? this.instance.budget : '',
      campaignStartTime: '',
      campaignEndTime: '',
      campaignDailyBudgetAmountLocalMicro: '',
      campaignTotalBudgetAmountLocalMicro: '',
      campaignDurationInDays: '',
      campaignFrequencyCap: '',
      campaignPurchaseOrderNumber: '',
      campaignStandardDelivery: true,
      campaignBudgetType: this.instance ? this.instance.budgetType : 'DAILY',
      campaignStrategy: this.instance ? this.instance.biddingStrategy : 'OPT_ENHANCED_CPC',
      campaignConversionCounting: this.instance ? this.instance.conversionRuleConfig.conversionCounting : 'ALL_PER_INTERACTION',
      adGroupID: adGroupID,
      adGroupName: adGroupName,
      bidAmount: bidAmount,
      scheduleType: 'IMMEDIATELY',
      title: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['title'] : '',
      displayUrl: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['displayUrl'] : '',
      targetUrl: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['landingUrl'] : '',
      description: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['description'] : '',
      brandname: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['sponsoredBy'] : '',
      imageUrlHQ: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['imageUrlHQ'] : '',
      imageHQ: {
        size: '',
        height: '',
        width: ''
      },
      imageUrl: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['imageUrl'] : '',
      image: {
        size: '',
        height: '',
        width: ''
      },
      previewData: '',
      attributes: [],
      dataAttributes: dataAttributes,
      openingFileSelector: '',
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      }
    }
  },
  methods: {
    openChooseFile(name) {
      this.openingFileSelector = name
      this.$modal.show(name)
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validSize(image, type) {
      if (type === 'HQ') {
        if (image.width === 1200 && image.height === 627) {
          return true;
        }
      } else {
        if (image.width === 627 && image.height === 627) {
          return true
        }
      }
      return false;
    },
    selectedHQFile() {
      let file = this.$refs.imageHQ.files[0];
      if (!file || file.type.indexOf('image/') !== 0) return;
      this.imageHQ.size = file.size;
      let reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = evt => {
        let img = new Image();
        img.onload = () => {
          this.imageHQ.width = img.width;
          this.imageHQ.height = img.height;
          if (this.validSize(this.imageHQ, 'HQ')) {
            let formData = new FormData();
            formData.append('file', this.$refs.imageHQ.files[0]);
            axios.post('/general/upload-files', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }).then((response) => {
                this.imageUrlHQ = response.data.path.replace('public', 'storage')
              })
              .catch((err) => {
                alert(err);
              });
          }
        }
        img.src = evt.target.result;
      }
      reader.onerror = evt => {
        console.error(evt);
      }
    },
    selectedFile() {
      let file = this.$refs.image.files[0];
      if (!file || file.type.indexOf('image/') !== 0) return;
      this.image.size = file.size;
      let reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = evt => {
        let img = new Image();
        img.onload = () => {
          this.image.width = img.width;
          this.image.height = img.height;
          if (this.validSize(this.image, '')) {
            let formData = new FormData();
            formData.append('file', this.$refs.image.files[0]);
            axios.post('/general/upload-files', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }).then((response) => {
                this.imageUrl = response.data.path.replace('public', 'storage')
              })
              .catch((err) => {
                alert(err);
              });
          }
        }
        img.src = evt.target.result;
      }
      reader.onerror = evt => {
        console.error(evt);
      }
    },
    loadPreview() {
      this.isLoading = true
      axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
        title: this.title,
        displayUrl: this.displayUrl,
        landingUrl: this.targetUrl,
        description: this.description,
        sponsoredBy: this.brandname,
        imageUrlHQ: this.imageUrlHQ,
        imageUrl: this.imageUrl,
        campaignLanguage: this.campaignLanguage
      }).then(response => {
        this.previewData = response.data.replace('height="800"', 'height="450"').replace('width="400"', 'width="100%"')
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    selectedAccountChanged() {
      this.getLanguages()
      this.getCountries()
      this.loadAdvertisers()
    },
    selectedAdvertiserChange() {
      this.loadFundingInstruments();
    },
    getLanguages() {
      this.isLoading = true
      this.languages = []
      axios.get(`/general/languages?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        if (response.data) {
          this.languages = response.data.map(language => {
            return {
              id: language.value || language.code,
              text: language.name.toUpperCase()
            }
          })
        }
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    getCountries() {
      this.isLoading = true
      this.countries = []
      axios.get(`/general/countries?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        if (response.data) {
          this.countries = response.data.map(country => {
            return {
              id: country.woeid,
              text: country.name
            }
          })
        }
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    removeAttibute(index) {
      this.attributes.splice(index, 1);
    },
    addNewAttibute() {
      this.attributes.push({
        gender: '',
        age: '',
        device: ''
      })
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
    signUp() {
      this.isLoading = true
      axios.post('/account/sign-up', {
        provider: this.selectedProvider,
        account: this.selectedAccount
      }).then(response => {
        alert('New advertiser has been saved!')
        this.selectedAdvertiser = response.data.id
        this.loadAdvertisers()
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    submitStep1() {
      const step1Data = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.selectedAdvertiser,
        campaignBudget: this.campaignBudget,
        campaignBudgetType: this.campaignBudgetType,
        campaignName: this.campaignName,
        adGroupID: this.adGroupID,
        adGroupName: this.adGroupName,
        adID: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['id'] : '',
        bidAmount: this.bidAmount,
        campaignStatus: this.campaignStatus,
        campaignLanguage: this.campaignLanguage,
        campaignStrategy: this.campaignStrategy,
        campaignLocation: this.campaignLocation,
        campaignGender: this.campaignGender,
        campaignAge: this.campaignAge,
        campaignDevice: this.campaignDevice,
        campaignConversionCounting: this.campaignConversionCounting,
        scheduleType: this.scheduleType,
        campaignStartTime: this.campaignStartTime,
        campaignEndTime: this.campaignEndTime
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {
        displayUrl: this.displayUrl,
        targetUrl: this.targetUrl,
        title: this.title,
        brandname: this.brandname,
        description: this.description,
        imageUrlHQ: this.imageUrlHQ,
        imageUrl: this.imageUrl,
        dataAttributes: this.dataAttributes
      }
      this.postData = {...this.postData, ...step2Data }
      this.attributes[0] = {
        gender: this.campaignGender,
        age: this.campaignAge,
        device: this.campaignDevice
      }
      this.currentStep = 3
    },
    submitStep3() {
      const step3Data = {
        attributes: this.attributes
      }
      this.postData = {...this.postData, ...step3Data }
      this.currentStep = 4
    },
    submitStep4() {
      this.isLoading = true
      let url = '/campaigns';

      if (this.action == 'edit') {
        url += '/update/' + this.instance.instance_id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          alert('Save successfully!');
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