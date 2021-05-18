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
                    <select name="advertiser" class="form-control" v-model="selectedAdvertiser" :disabled="instance">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in advertisers" :key="advertiser.id">{{ advertiser.id }} - {{ advertiser.advertiserName }}</option>
                    </select>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser">
                    <input type="text" name="advertiser_name" v-model="advertiserName" class="form-control" placeholder="Enter advertiser name...">
                  </div>
                  <div class="col-sm-2" v-if="saveAdvertiser && !instance">
                    <button type="button" class="btn btn-primary" @click.prevent="saveAdvertiser = !saveAdvertiser">Create New</button>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser && advertiserName">
                    <button type="button" class="btn btn-success" @click.prevent="signUp()">Save</button>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser">
                    <button type="button" class="btn btn-warning" @click.prevent="saveAdvertiser = !saveAdvertiser">Cancel</button>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-sm-8">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="type" class="col-sm-2 control-label mt-2">Type</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignType === 'NATIVE' }">
                        <input type="radio" name="type" id="campaignType1" autocomplete="off" value="NATIVE" v-model="campaignType"> Native Only
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignType === 'SEARCH' }">
                        <input type="radio" name="type" id="campaignType2" autocomplete="off" value="SEARCH" v-model="campaignType"> Search Only
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignType === 'SEARCH_AND_NATIVE' }">
                        <input type="radio" name="type" id="campaignType3" autocomplete="off" value="SEARCH_AND_NATIVE" v-model="campaignType"> Search and Native
                      </label>
                    </div>
                  </div>
                </div>
                <h2>Define your audience</h2>
                <div class="form-group row">
                  <label for="language" class="col-sm-2 control-label mt-2">Language</label>
                  <div class="col-sm-8">
                    <select2 id="language" name="language" :options="languages" v-model="campaignLanguage"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="location" class="col-sm-2 control-label mt-2">Location</label>
                  <div class="col-sm-8">
                    <select2 id="location" name="location" v-model="campaignLocation" :options="countries" :settings="{ multiple: true }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="gender" class="col-sm-2 control-label mt-2">Gender</label>
                  <div class="col-sm-8">
                    <select2 id="gender" name="gender" v-model="campaignGender" :options="genders" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="age" class="col-sm-2 control-label mt-2">Age</label>
                  <div class="col-sm-8">
                    <select2 id="age" name="age" v-model="campaignAge" :options="ages" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select2 name="device" v-model="campaignDevice" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <h2>Campaign settings</h2>
                <div class="form-group row">
                  <label for="objective" class="col-sm-2 control-label mt-2">Objective</label>
                  <div class="col-sm-8">
                    <select name="objective" class="form-control" v-model="campaignObjective">
                      <option value="VISIT_WEB">Visit Web</option>
                      <option value="VISIT_OFFER">Visit Offer</option>
                      <option value="PROMOTE_BRAND">Promote Brand</option>
                      <option value="INSTALL_APP">Install App</option>
                      <option value="MAIL_SPONSORED">Mail Sponsored</option>
                      <option value="REENGAGE_APP">Reengage App</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="budget" class="col-sm-2 control-label mt-2">Budget</label>
                  <div class="col-sm-2">
                    <input type="number" name="budget" min="40" class="form-control" v-model="campaignBudget" />
                  </div>
                  <div class="col-sm-4">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignBudgetType === 'DAILY' }">
                        <input type="radio" name="type" id="campaignBudgetType1" autocomplete="off" value="DAILY" v-model="campaignBudgetType"> Per Day
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignBudgetType === 'MONTHLY' }">
                        <input type="radio" name="type" id="campaignBudgetType2" autocomplete="off" value="MONTHLY" v-model="campaignBudgetType"> Per Month
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignBudgetType === 'LIFETIME' }">
                        <input type="radio" name="type" id="campaignBudgetType3" autocomplete="off" value="LIFETIME" v-model="campaignBudgetType"> In Total
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="bid_strategy" class="col-sm-2 control-label mt-2">Bid Strategy</label>
                  <div class="col-sm-8">
                    <select name="bid_strategy" class="form-control" v-model="campaignStrategy">
                      <option value="OPT_ENHANCED_CPC">Enhanced CPC</option>
                      <option value="OPT_POST_INSTALL">Post Install</option>
                      <option value="OPT_CONVERSION">Conversion</option>
                      <option value="OPT_CLICK">Click</option>
                      <option value="MAX_OPT_CONVERSION">Max Conversion</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="bid_adjustment" class="col-sm-2 control-label mt-2">Native Bid Adjustment</label>
                  <div class="col-sm-8">
                    <div class="form-group row">
                      <div class="col">
                        <treeselect :options="supportedSites" :disable-branch-nodes="true" @select="supportedSiteChanged" placeholder="Add publisher..." />
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="col">
                        <select2 id="network_setting" name="network_setting" v-model="networkSetting" :options="networkSettings" @change="networkSettingChanged" placeholder="Load from setting..." />
                      </div>
                    </div>
                    <div class="row">
                      <div class="col">
                        <div class="row">
                          <label class="col-sm-4 mt-2">Publishers</label>
                          <label class="col-sm-8 mt-2 text-center">Bid Adjustments</label>
                        </div>
                        <div class="row">
                          <label for="bid_adjustment_group_1a" class="col-sm-5 control-label mt-2">Group 1A <small>(+800%)</small></label>
                          <select class="form-control col-sm-2">
                            <option value="1">Increase By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_group_1a" min="0" max="800" class="form-control" v-model="campaignSupplyGroup1A" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <label for="bid_adjustment_group_1b" class="col-sm-5 control-label mt-2">Group 1B <small>(+600% — -40%)</small></label>
                          <select class="form-control col-sm-2" v-model="incrementType1b">
                            <option value="1">Increase By</option>
                            <option value="-1">Decrease By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_group_1b" min="0" max="600" class="form-control" v-model="campaignSupplyGroup1B" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <label for="bid_adjustment_group_2a" class="col-sm-5 control-label mt-2">Group 2A <small>(+800% — -30%)</small></label>
                          <select class="form-control col-sm-2" v-model="incrementType2a">
                            <option value="1">Increase By</option>
                            <option value="-1">Decrease By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_group_2a" min="0" max="800" class="form-control" v-model="campaignSupplyGroup2A" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <label for="bid_adjustment_group_2b" class="col-sm-5 control-label mt-2">Group 2B <small>(+600% — -70%)</small></label>
                          <select class="form-control col-sm-2" v-model="incrementType2b">
                            <option value="1">Increase By</option>
                            <option value="-1">Decrease By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_group_2b" min="0" max="600" class="form-control" v-model="campaignSupplyGroup2B" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <label for="bid_adjustment_group_3a" class="col-sm-5 control-label mt-2">Group 3A <small>(+800% — -50%)</small></label>
                          <select class="form-control col-sm-2" v-model="incrementType3a">
                            <option value="1">Increase By</option>
                            <option value="-1">Decrease By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_group_3a" min="0" max="800" class="form-control" v-model="campaignSupplyGroup3A" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                          <label for="bid_adjustment_group_3b" class="col-sm-5 control-label mt-2">Group 3B <small>(+600% — -80%)</small></label>
                          <select class="form-control col-sm-2" v-model="incrementType3b">
                            <option value="1">Increase By</option>
                            <option value="-1">Decrease By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_group_3b" min="0" max="600" class="form-control" v-model="campaignSupplyGroup3B" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                        </div>
                        <div class="row" v-for="(supportedSiteItem, index) in supportedSiteCollections" :key="index">
                          <label for="bid_adjustment_site_group" class="col-sm-5 control-label mt-2">{{ supportedSiteItem.label }} <small>{{ supportedSiteItem.subLabel }}</small></label>
                          <select class="form-control col-sm-2" v-model="supportedSiteItem.incrementType">
                            <option value="1">Increase By</option>
                            <option value="-1">Decrease By</option>
                          </select>
                          <div class="input-group col-sm-3 mb-1">
                            <input type="number" name="bid_adjustment_site_group" min="0" max="800" class="form-control" v-model="supportedSiteItem.bidModifier" />
                            <div class="input-group-append">
                              <span class="input-group-text">%</span>
                            </div>
                          </div>
                          <div class="col-sm-2">
                            <button class="btn btn-primary" @click.prevent="removeSupportedSite(index)">Remove</button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="site_block" class="col-sm-2 control-label mt-2">Site blocks</label>
                  <div class="col-sm-8">
                    <div class="row">
                      <div class="col">
                        <textarea class="form-control" rows="3" placeholder="Enter site block" v-model="campaignSiteBlock" :disabled="actionName == 'edit'"></textarea>
                        <small>Separate sites by break new line</small>
                      </div>
                    </div>
                    <div class="row mt-2">
                      <div class="col-sm-4" v-if="!saveNetworkSetting">
                        <input type="text" name="network_setting_name" v-model="networkSettingName" class="form-control" placeholder="Enter setting name">
                      </div>
                      <div class="col-sm-5" v-if="saveNetworkSetting && campaignSupplyGroupState">
                        <button type="button" class="btn btn-primary" @click.prevent="saveNetworkSetting = !saveNetworkSetting">Save these setting</button>
                      </div>
                      <div class="col-sm-3">
                        <button type="button" v-if="!saveNetworkSetting && networkSettingName && campaignSupplyGroupState" class="btn btn-success" @click.prevent="storeNetworkSetting()">Save</button>
                        <button type="button" v-if="!saveNetworkSetting" class="btn btn-warning" @click.prevent="saveNetworkSetting = !saveNetworkSetting">Cancel</button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="conversion_counting" class="col-sm-2 control-label mt-2">Conversion counting</label>
                  <div class="col-sm-8">
                    <select name="conversion_counting" class="form-control" v-model="campaignConversionCounting">
                      <option value="ALL_PER_INTERACTION">All per interaction</option>
                      <option value="ONE_PER_INTERACTION">One per interaction</option>
                    </select>
                  </div>
                </div>
                <h2>Create group</h2>
                <div class="form-group row">
                  <label for="ad_group_name" class="col-sm-2 control-label mt-2">Ad group name</label>
                  <div class="col-sm-8">
                    <input type="text" name="ad_group_name" class="form-control" v-model="adGroupName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="bid_strategy" class="col-sm-2 control-label mt-2">Bid strategy</label>
                  <div class="col-sm-8">
                    <p>{{ campaignStrategy }}</p>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="bid_cpc" class="col-sm-2 control-label mt-2">Bid (CPC)</label>
                  <div class="col-sm-8">
                    <input type="number" name="bid_cpc" min="1" class="form-control" v-model="bidAmount" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="schedule" class="col-sm-2 control-label mt-2">Schedule</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: scheduleType === 'IMMEDIATELY' }">
                        <input type="radio" name="schedule" id="scheduleType1" autocomplete="off" value="IMMEDIATELY" v-model="scheduleType"> Start running ads immediately
                      </label>
                      <label class="btn bg-olive" :class="{ active: scheduleType === 'CUSTOM' }">
                        <input type="radio" name="schedule" id="scheduleType2" autocomplete="off" value="CUSTOM" v-model="scheduleType"> Set a start and end date
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row" v-if="scheduleType === 'CUSTOM'">
                  <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
                  <div class="col-sm-4">
                    <VueCtkDateTimePicker id="start_date" v-model="campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-4">
                    <VueCtkDateTimePicker id="end_date" v-model="campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                </div>
              </div>
              <div class="card-body" v-if="currentStep == 2">
                <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in contents" :key="index">
                  <div class="row">
                    <div class="col-sm-7">
                      <div class="form-group row">
                        <label for="title" class="col-sm-4 control-label mt-2">Title</label>
                        <div class="col-sm-8">
                          <div class="row mb-2" v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                            <div class="col-sm-8">
                              <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.titleSet.id" />
                            </div>
                            <div class="col-sm-4">
                              <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle); loadPreviewEvent($event, index)" v-if="indexTitle > 0" :disabled="content.titleSet.id"><i class="fa fa-minus"></i></button>
                              <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.titles.length" :disabled="content.id || content.titleSet.id"><i class="fa fa-plus"></i></button>
                              <button type="button" class="btn btn-primary" v-if="indexTitle == 0" @click="loadCreativeSet('title', index)"><i class="far fa-folder-open"></i></button>
                            </div>
                          </div>
                          <div class="row" v-if="content.titleSet.id">
                            <div class="col">
                              <span class="selected-set">{{ content.titleSet.name }}<span class="close" @click="removeTitleSet(index)"><i class="fas fa-times"></i></span></span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name</label>
                        <div class="col-sm-8">
                          <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="content.brandname" v-on:blur="loadPreviewEvent($event, index)" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="description" class="col-sm-4 control-label mt-2">Description</label>
                        <div class="col-sm-8">
                          <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.descriptionSet.id"></textarea>
                          <div class="row mt-2">
                            <div class="col">
                              <span v-if="content.descriptionSet.id" class="selected-set">{{ content.descriptionSet.name }}<span class="close" @click="removeDescriptionSet(index)"><i class="fas fa-times"></i></span></span>
                            </div>
                          </div>
                          <button class="btn btn-primary btn-sm mt-2" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('description', index)">Load from Sets</button>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                        <div class="col-sm-8 text-center">
                          <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="content.displayUrl" v-on:blur="loadPreviewEvent($event, index)" />
                          <small class="text-danger" v-if="content.displayUrl && !validURL(content.displayUrl)">URL is invalid. You might need http/https at the beginning.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                        <div class="col-sm-8 text-center">
                          <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" v-on:blur="loadPreviewEvent($event, index)" />
                          <small class="text-danger" v-if="content.targetUrl && !validURL(content.targetUrl)">URL is invalid. You might need http/https at the beginning.</small>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label for="ad_type" class="col-sm-4 control-label mt-2">Ad Type</label>
                        <div class="col-sm-8">
                          <div class="btn-group btn-group-toggle">
                            <label class="btn bg-olive" :class="{ active: content.adType === 'IMAGE' }">
                              <input type="radio" name="ad_type" autocomplete="off" value="IMAGE" v-model="content.adType"> IMAGE
                            </label>
                            <label class="btn bg-olive" :class="{ active: content.adType === 'VIDEO' }">
                              <input type="radio" name="ad_type" autocomplete="off" value="VIDEO" v-model="content.adType"> VIDEO
                            </label>
                          </div>
                        </div>
                      </div>

                      <div v-if="content.adType == 'IMAGE'">
                        <fieldset class="mb-3 p-3 rounded border" v-for="(image, indexImage) in content.images" :key="indexImage">
                          <div class="form-group row">
                            <label for="image_hq_url" class="col-sm-4 control-label mt-2" v-html="'Image HQ URL <br> (1200 x 627 px)'"></label>
                            <div class="col-sm-8">
                              <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrlHQ" v-on:blur="loadPreviewEvent($event, index); validImageHQSizeEvent($event, index, indexImage)" :disabled="content.imageSet.id" />
                              <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQUrl', index, indexImage)" :disabled="content.imageSet.id">Choose File</button>
                            </div>
                            <div class="col-sm-8 offset-sm-4">
                              <small class="text-danger" v-if="image.imageUrlHQ && !validURL(image.imageUrlHQ)">URL is invalid. You might need http/https at the beginning.</small>
                              <small class="text-danger" v-if="!image.imageUrlHQState">Image is invalid. You might need an 1200 x 627 image.</small>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="image_url" class="col-sm-4 control-label mt-2" v-html="'Image URL <br> (627 x 627 px)'"></label>
                            <div class="col-sm-8">
                              <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrl" v-on:blur="loadPreviewEvent($event, index); validImageSizeEvent($event, index, indexImage)" :disabled="content.imageSet.id" />
                              <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageUrl', index, indexImage)" :disabled="content.imageSet.id">Choose File</button>
                            </div>
                            <div class="col-sm-8 offset-sm-4">
                              <small class="text-danger" v-if="image.imageUrl && !validURL(image.imageUrl)">URL is invalid. You might need http/https at the beginning.</small>
                              <small class="text-danger" v-if="!image.imageUrlState">Image is invalid. You might need an 627 x 627 image.</small>
                            </div>
                          </div>
                          <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeImage(index, indexImage); loadPreviewEvent($event, index)" v-if="indexImage > 0">Remove Image</button>
                        </fieldset>
                        <div class="row mt-2 mb-2">
                          <div class="col">
                            <span v-if="content.imageSet.id" class="selected-set">{{ content.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                          </div>
                        </div>
                        <button class="btn btn-primary btn-sm" @click.prevent="addImage(index)" :disabled="content.id || content.imageSet.id">Add Image</button>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                      </div>

                      <div v-if="content.adType == 'VIDEO'">
                        <fieldset class="mb-3 p-3 rounded border" v-for="(video, indexVideo) in content.videos" :key="indexVideo">
                          <div v-if="!['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(campaignObjective)">
                            <div class="form-group row">
                              <label for="image_portrait_url" class="col-sm-4 control-label mt-2" v-html="'Image HQ URL <br> vertical (portrait) 9:16'"></label>
                              <div class="col-sm-8">
                                <input type="text" name="image_portrait_url" placeholder="Enter a url" class="form-control" v-model="video.imagePortraitUrl" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.videoSet.id" />
                                <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imagePortraitUrl', index, indexVideo)" :disabled="content.videoSet.id">Choose File</button>
                              </div>
                            </div>
                            <div class="form-group row">
                              <label for="video_portrait_url" class="col-sm-4 control-label mt-2" v-html="'Video URL <br> vertical (portrait) 9:16'"></label>
                              <div class="col-sm-8">
                                <input type="text" name="video_portrait_url" placeholder="Enter video URL" class="form-control" v-model="video.videoPortraitUrl" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.videoSet.id" />
                                <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoPortraitUrl', index, indexVideo)" :disabled="content.videoSet.id">Choose File</button>
                              </div>
                            </div>
                          </div>
                          <div v-if="['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(campaignObjective)">
                            <div class="form-group row">
                              <label for="video_primary_url" class="col-sm-4 control-label mt-2">Video URL</label>
                              <div class="col-sm-8">
                                <input type="text" name="video_primary_url" placeholder="Enter video URL" class="form-control" v-model="video.videoPrimaryUrl" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.videoSet.id" />
                                <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoPrimaryUrl', index, indexVideo)" :disabled="content.videoSet.id">Choose File</button>
                              </div>
                            </div>
                          </div>
                          <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeVideo(index, indexVideo); loadPreviewEvent($event, index)" v-if="indexVideo > 0">Remove Video</button>
                        </fieldset>
                        <div class="row mt-2 mb-2">
                          <div class="col">
                            <span v-if="content.videoSet.id" class="selected-set">{{ content.videoSet.name }}<span class="close" @click="removeVideoSet(index)"><i class="fas fa-times"></i></span></span>
                          </div>
                        </div>
                        <button class="btn btn-primary btn-sm" @click.prevent="addVideo(index)" :disabled="content.id || content.videoSet.id">Add Video</button>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('video', index)">Load from Sets</button>
                      </div>
                    </div>
                    <div class="col-sm-5">
                      <h1>Preview</h1>
                      <div class="row mb-2" v-for="(preview, indexPreview) in content.adPreviews" :key="indexPreview">
                        <div class="col" v-html="preview.data"></div>
                      </div>
                    </div>
                  </div>
                  <div class="row" v-if="index > 0">
                    <div class="col text-right">
                      <button class="btn btn-warning btn-sm" @click.prevent="removeContent(index)">Remove</button>
                    </div>
                  </div>
                </fieldset>
                <button class="btn btn-primary btn-sm d-none" @click.prevent="addContent()">Add New</button>
              </div>
            </form>
          </div>
          <div class="card-body" v-if="currentStep == 3">
            <div class="row mb-2">
              <div class="col-sm-12">
                <h4>Main Variation</h4>
              </div>
              <div class="col-sm-12">
                <div class="form-group row">
                  <label for="variantGender" class="col-sm-4 control-label mt-2">Gender</label>
                  <div class="col-sm-8">
                    <select2 id="variantGender" name="variantGender" v-model="campaignGender" :options="genders" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
              </div>
              <div class="col-sm-12">
                <div class="form-group row">
                  <label for="variantAge" class="col-sm-4 control-label mt-2">Age</label>
                  <div class="col-sm-8">
                    <select2 id="variantAge" name="variantAge" v-model="campaignAge" :options="ages" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
              </div>
              <div class="col-sm-12 border-bottom">
                <div class="form-group row">
                  <label for="variantDevice" class="col-sm-4 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select2 id="variantDevice" name="variantDevice" v-model="campaignDevice" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="card-body" v-if="currentStep == 4">
            <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in contents" :key="index">
              <div class="row">
                <div class="col-sm-6" v-for="(preview, indexY) in content.adPreviews" :key="indexY">
                  <div v-html="preview.data"></div>
                </div>
              </div>
            </fieldset>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="!campaignNameState || !selectedAdvertiserState || !campaignBudgetState || !adGroupNameState || !bidAmountState">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary mr-2" @click.prevent="loadAllPreview" v-if="loadAllPreviewState">Load Preview</button>
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="!submitStep2State">Next</button>
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

    <div class="modal fade creative-set-modal" id="creative-set-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="col mt-3">
            <h1>Select Creative Set</h1>
          </div>
          <creative-set-sets :type="setType" @selectCreativeSet="selectCreativeSet"></creative-set-sets>
        </div>
      </div>
    </div>

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
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import Treeselect from '@riophae/vue-treeselect'
import { LOAD_ROOT_OPTIONS } from '@riophae/vue-treeselect'

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css'
import 'vue-loading-overlay/dist/vue-loading.css'

import '@riophae/vue-treeselect/dist/vue-treeselect.css'

let adPreviewCancels = []

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
    VueCtkDateTimePicker,
    Select2,
    Treeselect
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
    submitStep2State() {
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].brandname || (!this.contents[i].description && !this.contents[i].descriptionSet.id) || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        if (!this.contents[i].titleSet.id) {
          for (let j = 0; j < this.contents[i].titles.length; j++) {
            if (!this.contents[i].titles[j].title) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'IMAGE' && !this.contents[i].imageSet.id) {
          for (let j = 0; j < this.contents[i].images.length; j++) {
            if (!this.contents[i].images[j].imageUrlHQ || !this.validURL(this.contents[i].images[j].imageUrlHQ) || !this.contents[i].images[j].imageUrl || !this.validURL(this.contents[i].images[j].imageUrl) || !this.contents[i].images[j].imageUrlHQState || !this.contents[i].images[j].imageUrlState) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'VIDEO' && !this.contents[i].videoSet.id) {
          for (let j = 0; j < this.contents[i].videos.length; j++) {
            if (['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(this.campaignObjective) && !this.contents[i].videos[j].videoPrimaryUrl) {
              return false
            } else if (!['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(this.campaignObjective) && (!this.contents[i].videos[j].imagePortraitUrl || !this.contents[i].videos[j].videoPortraitUrl)) {
              return false
            }
          }
        }
      }

      return true
    },
    campaignSupplyGroupState() {
      if (this.campaignSupplyGroup1A || this.campaignSupplyGroup1B || this.campaignSupplyGroup2A || this.campaignSupplyGroup2B || this.campaignSupplyGroup3A || this.campaignSupplyGroup3B) {
        return true
      }

      for (let i = 0; i < this.supportedSiteCollections.length; i++) {
        if (this.supportedSiteCollections[i].bidModifier > 0) {
          return true
        }
      }

      return false
    },
    loadAllPreviewState() {
      for (let i = 0; i < this.contents.length; i++) {
        if (this.contents[i].titles.length > 1 || this.contents[i].images.length > 1 || this.contents[i].videos.length > 1) {
          return true
        }
      }
    }

  },
  mounted() {
    console.log('Component mounted.')
    let vm = this
    this.$root.$on('fm-selected-items', (value) => {
      const selectedFilePath = value[0].path
      if (this.openingFileSelector === 'imageHQUrl') {
        this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlHQ = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
        this.validImageSize(this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlHQ, 1200, 627).then(result => {
          this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlHQState = result
        });
        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'imageUrl') {
        this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
        this.validImageSize(this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrl, 627, 627).then(result => {
          this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlState = result
        });
        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'videoPrimaryUrl') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorIndexImage].videoPrimaryUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath

        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'imagePortraitUrl') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorIndexImage].imagePortraitUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath

        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'videoPortraitUrl') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorIndexImage].videoPortraitUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath

        this.loadPreview(this.fileSelectorIndex)
      }
      vm.$modal.hide('imageModal')
    });
    this.currentStep = this.step

    this.getLanguages()
    this.getCountries()
    this.getAdvertisers()
    this.getNetworkSettings()
    this.getBbsxdSupportedSites()

    if (this.instance) {
      for (let i = 0; i < this.instance.ads.length; i++) {
        this.loadPreview(i, true);
      }
    }
  },
  watch: {},
  data() {
    let campaignGender = [],
      campaignAge = [],
      campaignDevice = [],
      adGroupName = '',
      bidAmount = 1,
      campaignLocation = [],
      adGroupID = '',
      dataAttributes = [],
      campaignSupplyGroup1A = '',
      campaignSupplyGroup1B = '',
      campaignSupplyGroup2A = '',
      campaignSupplyGroup2B = '',
      campaignSupplyGroup3A = '',
      campaignSupplyGroup3B = '',
      incrementType1b = 1,
      incrementType2a = 1,
      incrementType2b = 1,
      incrementType3a = 1,
      incrementType3b = 1,
      campaignSiteBlock = '',
      supportedSiteCollections = [],
      contents = [{
        id: '',
        adType: 'IMAGE',
        titleSet: '',
        titles: [{
          title: ''
        }],
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        description: '',
        brandname: '',
        imageSet: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true
        }],
        videoSet: '',
        videos: [{
          videoPrimaryUrl: '',
          videoPortraitUrl: '',
          imagePortraitUrl: ''
        }],
        adPreviews: []
      }];
    if (this.instance) {
      let siteBlock = [];

      if (this.instance.adGroups.length > 0) {
        adGroupID = this.instance.adGroups[0]['id'];
        adGroupName = this.instance.adGroups[0]['adGroupName'];

        if (this.instance.adGroups[0]['bidSet']['bids'].length > 0) {
          bidAmount = this.instance.adGroups[0]['bidSet']['bids'][0]['value'];
        }
      }

      this.instance.attributes.forEach(attribute => {
        if (attribute.type === 'GENDER') {
          campaignGender.push(attribute.value)
        } else if (attribute.type === 'AGE') {
          campaignAge.push(attribute.value)
        } else if (attribute.type === 'DEVICE') {
          campaignDevice.push(attribute.value)
        } else if (attribute.type === 'WOEID') {
          campaignLocation.push(attribute.value)
        } else if (attribute.type === 'SUPPLY_GROUP') {
          if (attribute.value === 'GROUP_1_A') {
            campaignSupplyGroup1A = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)
          } else if (attribute.value === 'GROUP_1_B') {
            campaignSupplyGroup1B = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)

            if (campaignSupplyGroup1B < 0) {
              incrementType1b = -1
              campaignSupplyGroup1B = -campaignSupplyGroup1B
            }
          } else if (attribute.value === 'GROUP_2_A') {
            campaignSupplyGroup2A = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)

            if (campaignSupplyGroup2A < 0) {
              incrementType2a = -1
              campaignSupplyGroup2A = -campaignSupplyGroup2A
            }
          } else if (attribute.value === 'GROUP_2_B') {
            campaignSupplyGroup2B = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)

            if (campaignSupplyGroup2B < 0) {
              incrementType2b = -1
              campaignSupplyGroup2B = -campaignSupplyGroup2B
            }
          } else if (attribute.value === 'GROUP_3_A') {
            campaignSupplyGroup3A = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)

            if (campaignSupplyGroup3A < 0) {
              incrementType3a = -1
              campaignSupplyGroup3A = -campaignSupplyGroup3A
            }
          } else if (attribute.value === 'GROUP_3_B') {
            campaignSupplyGroup3B = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)

            if (campaignSupplyGroup3B < 0) {
              incrementType3b = -1
              campaignSupplyGroup3B = -campaignSupplyGroup3B
            }
          }
        } else if (attribute.type === 'SITE_BLOCK') {
          siteBlock.push(attribute.value);
        } else if (attribute.type === 'SITE_X_DEVICE' || attribute.type === 'SITE_GROUP_X_DEVICE') {
          let bidModifier = Math.round((attribute.bidModifier - bidAmount) / bidAmount * 100)
          let incrementType = 1

          if (bidModifier < 0) {
            incrementType = -1
            bidModifier = -bidModifier
          }
          supportedSiteCollections.push({
            label: '',
            subLabel: '(+800% — -80%)',
            key: attribute.value,
            type: attribute.type === 'SITE_X_DEVICE' ? 'site' : 'group',
            incrementType: incrementType,
            bidModifier: bidModifier
          })
        }

        dataAttributes.push(attribute.id);
      });

      if (siteBlock.length > 0) {
        campaignSiteBlock = siteBlock.join("\n");
      }

      contents = [];

      for (let i = 0; i < this.instance.ads.length; i++) {
        contents.push({
          id: this.instance.ads[i]['id'],
          adType: this.instance.ads[i]['videoPrimaryUrl'] || this.instance.ads[i]['imagePortraitUrl'] ? 'VIDEO': 'IMAGE',
          titleSet: this.instance.ads[i]['titleSet'] || '',
          titles: [{
            title: this.instance.ads[i]['title']
          }],
          displayUrl: this.instance.ads[i]['displayUrl'],
          targetUrl: this.instance.ads[i]['landingUrl'],
          description: this.instance.ads[i]['description'],
          descriptionSet:  this.instance.ads[i]['descriptionSet'] || '',
          brandname: this.instance.ads[i]['sponsoredBy'],
          imageSet:  this.instance.ads[i]['imageSet'] || '',
          images: [{
            imageUrlHQ: this.instance.ads[i]['imageUrlHQ'],
            imageUrlHQState: true,
            imageUrl: this.instance.ads[i]['imageUrl'],
            imageUrlState: true
          }],
          videoSet:  this.instance.ads[i]['videoSet'] || '',
          videos: [{
            videoPrimaryUrl: this.instance.ads[i]['videoPrimaryUrl'],
            videoPortraitUrl: this.instance.ads[i]['videoPortraitUrl'],
            imagePortraitUrl: this.instance.ads[i]['imagePortraitUrl']
          }],
          adPreviews: [],
        });
      }
    }

    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      currentStep: 1,
      saveAdvertiser: true,
      advertiserName: '',
      redtrackKey: '',
      languages: [],
      countries: [],
      genders: [{
        id: '',
        text: 'All'
      }, {
        id: 'MALE',
        text: 'Male'
      }, {
        id: 'FEMALE',
        text: 'Female'
      }],
      ages: [{
        id: '',
        text: 'All',
      }, {
        id: '18-24',
        text: '18-24',
      }, {
        id: '25-34',
        text: '25-34',
      }, {
        id: '35-44',
        text: '35-44',
      }, {
        id: '45-54',
        text: '45-54',
      }, {
        id: '55-64',
        text: '55-64',
      }, {
        id: '65-120',
        text: '65-120',
      }],
      devices: [{
        id: '',
        text: 'All',
      }, {
        id: 'SMARTPHONE',
        text: 'SMARTPHONE',
      }, {
        id: 'TABLET',
        text: 'TABLET',
      }, {
        id: 'DESKTOP',
        text: 'DESKTOP',
      }],
      advertisers: [],
      actionName: this.action,
      selectedAdvertiser: this.instance ? this.instance.advertiserId : '',
      campaignName: this.instance ? this.instance.campaignName : '',
      campaignType: this.instance ? this.instance.channel : 'NATIVE',
      campaignLanguage: this.instance ? this.instance.language : 'en',
      campaignLocation: campaignLocation,
      campaignGender: campaignGender,
      campaignAge: campaignAge,
      campaignDevice: campaignDevice,
      campaignBudget: this.instance ? this.instance.budget : '',
      campaignObjective: this.instance ? this.instance.objective : 'VISIT_WEB',
      campaignStartDate: this.instance ? this.instance.start_date : this.$moment().format('YYYY-MM-DD'),
      campaignEndDate: this.instance ? this.instance.end_date : '',
      campaignBudgetType: this.instance ? this.instance.budgetType : 'DAILY',
      campaignStrategy: this.instance ? this.instance.biddingStrategy : 'OPT_CLICK',
      campaignConversionCounting: this.instance ? this.instance.conversionRuleConfig.conversionCounting : 'ALL_PER_INTERACTION',
      campaignSupplyGroup1A: campaignSupplyGroup1A,
      campaignSupplyGroup1B: campaignSupplyGroup1B,
      campaignSupplyGroup2A: campaignSupplyGroup2A,
      campaignSupplyGroup2B: campaignSupplyGroup2B,
      campaignSupplyGroup3A: campaignSupplyGroup3A,
      campaignSupplyGroup3B: campaignSupplyGroup3B,
      incrementType1b: incrementType1b,
      incrementType2a: incrementType2a,
      incrementType2b: incrementType2b,
      incrementType3a: incrementType3a,
      incrementType3b: incrementType3b,
      campaignSiteBlock: campaignSiteBlock,
      adGroupID: adGroupID,
      adGroupName: adGroupName,
      bidAmount: bidAmount,
      scheduleType: 'IMMEDIATELY',
      contents: contents,
      dataAttributes: dataAttributes,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      fileSelectorIndexImage: 0,
      networkSettings: [],
      networkSettingData: [],
      networkSetting: null,
      saveNetworkSetting: true,
      networkSettingName: '',
      supportedSites: [],
      supportedSiteCollections: supportedSiteCollections,
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'en'
      },
      adSelectorIndex: 0,
      setType: 'image'
    }
  },
  methods: {
    openChooseFile(name, index, indexImage) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.fileSelectorIndexImage = indexImage
      this.$modal.show('imageModal')
    },
    loadCreativeSet(type, index) {
      this.setType = type
      this.adSelectorIndex = index
      $('#creative-set-modal').modal('show')
    },
    selectCreativeSet(set) {
      if (this.setType == 'title') {
        this.contents[this.adSelectorIndex].titleSet = set
        this.loadTitleSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }
      if (this.setType == 'image') {
        this.contents[this.adSelectorIndex].imageSet = set
        this.loadImageSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }
      if (this.setType == 'video') {
        this.contents[this.adSelectorIndex].videoSet = set
        this.loadVideoSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }
      if (this.setType == 'description') {
        this.contents[this.adSelectorIndex].descriptionSet = set
        this.loadDescriptionSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }

      $('#creative-set-modal').modal('hide')
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validImageSize(imageUrl, width, height) {
      return new Promise((resolve) => {
        var image = new Image();
        image.onload = function() {
          resolve(this.width == width && this.height == height);
        };
        image.src = imageUrl;
      });
    },
    addContent() {
      this.contents.push({
        id: '',
        adType: 'IMAGE',
        titleSet: '',
        titles: [{
          title: ''
        }],
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        description: '',
        brandname: '',
        imageSet: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true
        }],
        videoSet: '',
        videos: [{
          videoPrimaryUrl: '',
          videoPortraitUrl: '',
          imagePortraitUrl: ''
        }],
        adPreviews: []
      })
    },
    removeContent(index) {
      this.contents.splice(index, 1);
    },
    addTitle(index) {
      this.contents[index].titles.push({
        title: ''
      })
    },
    removeTitle(index, indexTitle) {
      this.contents[index].titles.splice(indexTitle, 1)
    },
    addImage(index) {
      this.contents[index].images.push({
        imageUrlHQ: '',
        imageUrlHQState: true,
        imageUrl: '',
        imageUrlState: true
      })
    },
    removeImage(index, indexImage) {
      this.contents[index].images.splice(indexImage, 1)
    },
    addVideo(index) {
      this.contents[index].videos.push({
        videoPrimaryUrl: '',
        videoPortraitUrl: '',
        imagePortraitUrl: ''
      })
    },
    removeVideo(index, indexVideo) {
      this.contents[index].videos.splice(indexVideo, 1)
    },
    removeSupportedSite(index) {
      this.supportedSiteCollections.splice(index, 1);
    },
    removeImageSet(index) {
      this.contents[index].imageSet = ''
      this.contents[index].images = [{
        imageUrlHQ: '',
        imageUrlHQState: true,
        imageUrl: '',
        imageUrlState: true
      }]
    },
    removeVideoSet(index) {
      this.contents[index].videoSet = ''
      this.contents[index].videos = [{
        videoPrimaryUrl: '',
        videoPortraitUrl: '',
        imagePortraitUrl: ''
      }]
    },
    removeTitleSet(index) {
      this.contents[index].titleSet = ''
      this.contents[index].titles = [{
        title: ''
      }]
    },
    removeDescriptionSet(index) {
      this.contents[index].descriptionSet = ''
    },
    loadPreviewEvent(event, index) {
      if (this.contents[index].titles.length > 1 || this.contents[index].images.length > 1 || this.contents[index].videos.length > 1) {
        return
      }
      this.loadPreview(index)
    },
    validImageHQSizeEvent(event, index, indexImage) {
      this.validImageSize(this.contents[index].images[indexImage].imageUrlHQ, 1200, 627).then(result => {
        this.contents[index].images[indexImage].imageUrlHQState = result
      });
    },
    validImageSizeEvent(event, index, indexImage) {
      this.validImageSize(this.contents[index].images[indexImage].imageUrl, 627, 627).then(result => {
        this.contents[index].images[indexImage].imageUrlState = result
      });
    },
    loadTitleSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/title-sets/${this.contents[index].titleSet.id}`).then(response => {
        this.contents[index].titleSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadImageSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/image-sets/${this.contents[index].imageSet.id}`).then(response => {
        this.contents[index].imageSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadVideoSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/video-sets/${this.contents[index].videoSet.id}`).then(response => {
        this.contents[index].videoSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadDescriptionSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/description-sets/${this.contents[index].descriptionSet.id}`).then(response => {
        this.contents[index].descriptionSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadAllPreview() {
      for (let i = 0; i < this.contents.length; i++) {
        this.loadPreview(i, true)
      }
    },
    loadPreview(index, firstLoad = false) {
      if (!firstLoad && adPreviewCancels.length > 0) {
        for (let i = 0; i < adPreviewCancels.length; i++) {
          adPreviewCancels[i]()
        }
      }
      this.isLoading = true
      this.contents[index].adPreviews = [];

      let titles = [], description = ''

      if (this.contents[index].titleSet.id) {
        titles = this.contents[index].titleSet.sets
      } else {
        titles = this.contents[index].titles
      }

      if (this.contents[index].descriptionSet.id) {
        description = this.contents[index].descriptionSet.sets[0].description
      } else {
        description = this.contents[index].description
      }

      if (this.contents[index].adType == 'IMAGE') {
        let images = []

        if (this.contents[index].imageSet.id) {
          images = this.contents[index].imageSet.sets
        } else {
          images = this.contents[index].images
        }

        for (let i = 0; i < titles.length; i++) {
          for (let y = 0; y < images.length; y++) {
            axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
              title: titles[i].title,
              adType: this.contents[index].adType,
              displayUrl: this.contents[index].displayUrl,
              landingUrl: this.contents[index].targetUrl,
              description: description,
              sponsoredBy: this.contents[index].brandname,
              imageUrlHQ: this.contents[index].imageSet.id ? process.env.MIX_APP_URL + (images[y].optimiser == 0 ? '/storage/images/' + images[y].hq_1200x627_image : '/storage/images/creatives/1200x627/' + images[y].hq_image) : images[y].imageUrlHQ,
              imageUrl: this.contents[index].imageSet.id ? process.env.MIX_APP_URL + '/storage/images/' + images[y].image : images[y].imageUrl,
              campaignObjective: this.campaignObjective,
              campaignLanguage: this.campaignLanguage
            }, {
              cancelToken: new axios.CancelToken(function executor(c) {
                adPreviewCancels.push(c);
              })
            }).then(response => {
              this.contents[index].adPreviews.push({
                data: response.data.replace('height="800"', 'height="450"').replace('width="400"', 'width="100%"')
              })
            }).catch(err => {
              console.log(err)
            }).finally(() => {
              this.isLoading = false
            })
          }
        }
      } else {
        let videos = []

        if (this.contents[index].videoSet.id) {
          videos = this.contents[index].videoSet.sets
        } else {
          videos = this.contents[index].videos
        }

        for (let i = 0; i < titles.length; i++) {
          for (let y = 0; y < videos.length; y++) {
            axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
              title: titles[i].title,
              adType: this.contents[index].adType,
              displayUrl: this.contents[index].displayUrl,
              landingUrl: this.contents[index].targetUrl,
              description: description,
              sponsoredBy: this.contents[index].brandname,
              videoPrimaryUrl: videos[y].videoPrimaryUrl,
              videoPortraitUrl: videos[y].videoPortraitUrl,
              imagePortraitUrl: this.contents[index].videoSet.id ? process.env.MIX_APP_URL + '/storage/images/' + videos[y].portrait_image : videos[y].imagePortraitUrl,
              campaignObjective: this.campaignObjective,
              campaignLanguage: this.campaignLanguage
            }, {
              cancelToken: new axios.CancelToken(function executor(c) {
                adPreviewCancels.push(c);
              })
            }).then(response => {
              this.contents[index].adPreviews.push({
                data: response.data.replace('height="800"', 'height="450"').replace('width="400"', 'width="100%"')
              })
            }).catch(err => {
              console.log(err)
            }).finally(() => {
              this.isLoading = false
            })
          }
        }
      }
    },
    getLanguages() {
      this.isLoading = true
      this.languages = []
      axios.get(`/general/languages?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        if (response.data) {
          this.languages = response.data.map(language => {
            return {
              id: language.value || language.code,
              text: language.name ? language.name.toUpperCase() : language.value
            }
          })
        }
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },

    getBbsxdSupportedSites() {
      this.isLoading = true
      axios.get(`/general/bdsxd-supported-sites?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        if (response.data) {
          this.supportedSites = response.data

          let total = 0

          for (let i = 0; i < response.data.length; i++) {
            for (let j = 0; j < response.data[i].children.length; j++) {
              for (let l = 0; l < this.supportedSiteCollections.length; l++) {
                if (response.data[i].children[j].id == this.supportedSiteCollections[l].key) {
                  this.supportedSiteCollections[l].label = response.data[i].children[j].label
                  total++
                }

                if (total > this.supportedSiteCollections.length) {
                  break
                }
              }

              if (total > this.supportedSiteCollections.length) {
                break
              }
            }

            if (total > this.supportedSiteCollections.length) {
              break
            }
          }
        }
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    getNetworkSettings() {
      this.isLoading = true
      axios.get(`/general/network-setting?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.networkSettingData = response.data
        if (response.data) {
          this.networkSettings = response.data.map((item, index) => {
            return {
              id: index,
              text: item.name
            }
          })
        }
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    networkSettingChanged() {
      let data = this.networkSettingData[this.networkSetting]
      this.campaignSupplyGroup1A = data.group_1a
      this.campaignSupplyGroup1B = data.group_1b
      this.campaignSupplyGroup2A = data.group_2a
      this.campaignSupplyGroup2B = data.group_2b
      this.campaignSupplyGroup3A = data.group_3a
      this.campaignSupplyGroup3B = data.group_3b

      if (this.campaignSupplyGroup1B < 0) {
        this.incrementType1b = -1
        this.campaignSupplyGroup1B = -this.campaignSupplyGroup1B
      }

      if (this.campaignSupplyGroup2A < 0) {
        this.incrementType2a = -1
        this.campaignSupplyGroup2A = -this.campaignSupplyGroup2A
      }

      if (this.campaignSupplyGroup2B < 0) {
        this.incrementType2b = -1
        this.campaignSupplyGroup2B = -this.campaignSupplyGroup2B
      }

      if (this.campaignSupplyGroup3A < 0) {
        this.incrementType3a = -1
        this.campaignSupplyGroup3A = -this.campaignSupplyGroup3A
      }

      if (this.campaignSupplyGroup3B < 0) {
        this.incrementType3b = -1
        this.campaignSupplyGroup3B = -this.campaignSupplyGroup3B
      }

      this.campaignSiteBlock = data.site_block

      if (data.site_group) {
        this.supportedSiteCollections = JSON.parse(data.site_group)

        for (let i = 0; i < this.supportedSiteCollections.length; i++) {
          if (this.supportedSiteCollections[i].bidModifier < 0) {
            this.supportedSiteCollections[i].bidModifier = -this.supportedSiteCollections[i].bidModifier
          }
        }
      }
    },
    supportedSiteChanged(node, instanceId) {
      this.supportedSiteCollections.push({
        label: node.label,
        subLabel: '(+800% — -80%)',
        key: node.id,
        type: node.type,
        incrementType: 1,
        bidModifier: ''
      })
    },
    storeNetworkSetting() {
      this.isLoading = true
      axios.post(`/general/network-setting?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
        networkSettingName: this.networkSettingName,
        campaignSiteBlock: this.campaignSiteBlock,
        campaignSupplyGroup1A: this.campaignSupplyGroup1A,
        campaignSupplyGroup1B: this.campaignSupplyGroup1B,
        campaignSupplyGroup2A: this.campaignSupplyGroup2A,
        campaignSupplyGroup2B: this.campaignSupplyGroup2B,
        campaignSupplyGroup3A: this.campaignSupplyGroup3A,
        campaignSupplyGroup3B: this.campaignSupplyGroup3B,
        incrementType1b: this.incrementType1b,
        incrementType2a: this.incrementType2a,
        incrementType2b: this.incrementType2b,
        incrementType3a: this.incrementType3a,
        incrementType3b: this.incrementType3b,
        supportedSiteCollections: this.supportedSiteCollections
      }).then(response => {
        this.saveNetworkSetting = true
        this.getNetworkSettings()
      }).catch(err => {}).finally(() => {
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
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    getAdvertisers() {
      this.advertisers = []
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.advertisers = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    signUp() {
      this.isLoading = true
      axios.post('/account/sign-up', {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        name: this.advertiserName
      }).then(response => {
        alert('New advertiser has been saved!')
        this.advertiserName = ''
        this.saveAdvertiser = true
        this.getAdvertisers()
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    submitStep1() {
      const step1Data = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.selectedAdvertiser,
        campaignBudget: this.campaignBudget,
        campaignObjective: this.campaignObjective,
        campaignBudgetType: this.campaignBudgetType,
        campaignName: this.campaignName,
        adGroupID: this.adGroupID,
        adGroupName: this.adGroupName,
        bidAmount: this.bidAmount,
        campaignType: this.campaignType,
        campaignSupplyGroupState: this.campaignSupplyGroupState,
        campaignLanguage: this.campaignLanguage,
        campaignStrategy: this.campaignStrategy,
        campaignLocation: this.campaignLocation,
        campaignGender: this.campaignGender,
        campaignAge: this.campaignAge,
        campaignDevice: this.campaignDevice,
        campaignConversionCounting: this.campaignConversionCounting,
        scheduleType: this.scheduleType,
        campaignStartDate: this.campaignStartDate,
        campaignEndDate: this.campaignEndDate,
        campaignSupplyGroup1A: this.campaignSupplyGroup1A,
        campaignSupplyGroup1B: this.campaignSupplyGroup1B,
        campaignSupplyGroup2A: this.campaignSupplyGroup2A,
        campaignSupplyGroup2B: this.campaignSupplyGroup2B,
        campaignSupplyGroup3A: this.campaignSupplyGroup3A,
        campaignSupplyGroup3B: this.campaignSupplyGroup3B,
        supportedSiteCollections: this.supportedSiteCollections,
        campaignSiteBlock: this.campaignSiteBlock,
        incrementType1b: this.incrementType1b,
        incrementType2a: this.incrementType2a,
        incrementType2b: this.incrementType2b,
        incrementType3a: this.incrementType3a,
        incrementType3b: this.incrementType3b,
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {
        contents: this.contents,
        dataAttributes: this.dataAttributes
      }
      this.postData = {...this.postData, ...step2Data }
      this.currentStep = 3
    },
    submitStep3() {
      this.currentStep = 4
    },
    submitStep4() {
      this.isLoading = true
      let url = '/campaigns';

      for (let i = 0; i < this.contents.length; i++) {
        if (this.contents[i].titleSet.id) {
          delete this.contents[i].titleSet.sets
        }
        if (this.contents[i].imageSet.id) {
          delete this.contents[i].imageSet.sets
        }
        if (this.contents[i].videoSet.id) {
          delete this.contents[i].videoSet.sets
        }
        if (this.contents[i].descriptionSet.id) {
          delete this.contents[i].descriptionSet.sets
        }

        delete this.contents[i].adPreviews
      }

      if (this.action == 'edit') {
        url += '/update/' + this.instance.instance_id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0]);
        } else {
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = '/campaigns';
          });
        }
      }).catch(error => {}).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>

<style>
.select2 {
  width: 100% !important;
}

.selected-set {
  position: relative;
}

.selected-set .close {
  position: absolute;
  right: -20px;
  top: -3px;
  cursor: pointer;
  font-size: 0.9em;
}
</style>
