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
                              <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" v-on:blur="loadPreviewEvent($event, index)" />
                            </div>
                            <div class="col-sm-4">
                              <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle); loadPreviewEvent($event, index)" v-if="indexTitle > 0"><i class="fa fa-minus"></i></button>
                              <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.titles.length"><i class="fa fa-plus"></i></button>
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
                          <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description" v-on:blur="loadPreviewEvent($event, index)"></textarea>
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
                      <fieldset class="mb-3 p-3 rounded border" v-for="(image, indexImage) in content.images" :key="indexImage">
                        <div class="form-group row">
                          <label for="image_hq_url" class="col-sm-4 control-label mt-2" v-html="'Image HQ URL <br> (1200 x 627 px)'"></label>
                          <div class="col-sm-8">
                            <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrlHQ" v-on:blur="loadPreviewEvent($event, index); validImageHQSizeEvent($event, index)" />
                            <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQUrl', index, indexImage)">Choose File</button>
                          </div>
                          <div class="col-sm-8 offset-sm-4">
                            <small class="text-danger" v-if="image.imageUrlHQ && !validURL(image.imageUrlHQ)">URL is invalid. You might need http/https at the beginning.</small>
                            <small class="text-danger" v-if="!image.imageUrlHQState">Image is invalid. You might need an 1200x627 image.</small>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="image_url" class="col-sm-4 control-label mt-2" v-html="'Image URL <br> (627 x 627 px)'"></label>
                          <div class="col-sm-8">
                            <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrl" v-on:blur="loadPreviewEvent($event, index); validImageSizeEvent($event, index)" />
                            <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageUrl', index, indexImage)">Choose File</button>
                          </div>
                          <div class="col-sm-8 offset-sm-4">
                            <small class="text-danger" v-if="image.imageUrl && !validURL(image.imageUrl)">URL is invalid. You might need http/https at the beginning.</small>
                            <small class="text-danger" v-if="!image.imageUrlState">Image is invalid. You might need an 627x627 image.</small>
                          </div>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeImage(index, indexImage); loadPreviewEvent($event, index)" v-if="indexImage > 0">Remove Image</button>
                      </fieldset>
                      <button class="btn btn-primary btn-sm" @click.prevent="addImage(index)">Add Image</button>
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

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css'
import 'vue-loading-overlay/dist/vue-loading.css'

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
    submitStep2State() {
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].brandname || !this.contents[i].description || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        for (let j = 0; j < this.contents[i].titles.length; j++) {
          if (!this.contents[i].titles[j].title) {
            return false
          }
        }

        for (let j = 0; j < this.contents[i].images.length; j++) {
          if (!this.contents[i].images[j].imageUrlHQ || !this.validURL(this.contents[i].images[j].imageUrlHQ) || !this.contents[i].images[j].imageUrl || !this.validURL(this.contents[i].images[j].imageUrl) || !this.contents[i].images[j].imageUrlHQState || !this.contents[i].images[j].imageUrlState) {
            return false
          }
        }
      }

      return true
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
      vm.$modal.hide('imageModal')
    });
    this.currentStep = this.step

    this.getLanguages()
    this.getCountries()
    this.getAdvertisers()

    if (this.instance) {
      for (let i = 0; i < this.instance.ads.length; i++) {
        this.loadPreview(i, true);
      }
    }
  },
  watch: {

  },
  data() {
    let campaignGender = [],
      campaignAge = [],
      campaignDevice = [],
      adGroupName = '',
      bidAmount = 1,
      campaignLocation = [],
      adGroupID = '',
      dataAttributes = [],
      contents = [{
        id: '',
        titles: [{
          title: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        brandname: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true,
          existing: false
        }],
        adPreviews: []
      }];
    if (this.instance) {
      this.instance.attributes.forEach(attribute => {
        if (attribute.type === 'GENDER') {
          campaignGender.push(attribute.value);
        } else if (attribute.type === 'AGE') {
          campaignAge.push(attribute.value);
        } else if (attribute.type === 'DEVICE') {
          campaignDevice.push(attribute.value);
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

      contents = [];

      for (let i = 0; i < this.instance.ads.length; i++) {
        contents.push({
          id: this.instance.ads[i]['id'],
          titles: [{
            title: this.instance.ads[i]['title'],
            existing: true
          }],
          displayUrl: this.instance.ads[i]['displayUrl'],
          targetUrl: this.instance.ads[i]['landingUrl'],
          description: this.instance.ads[i]['description'],
          brandname: this.instance.ads[i]['sponsoredBy'],
          images: [{
            imageUrlHQ: this.instance.ads[i]['imageUrlHQ'],
            imageUrlHQState: true,
            imageUrl: this.instance.ads[i]['imageUrl'],
            imageUrlState: true,
            existing: true
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
      campaignStartDate: this.instance ? this.instance.start_date : this.$moment().format('YYYY-MM-DD'),
      campaignEndDate: this.instance ? this.instance.end_date : '',
      campaignBudgetType: this.instance ? this.instance.budgetType : 'DAILY',
      campaignStrategy: this.instance ? this.instance.biddingStrategy : 'OPT_CLICK',
      campaignConversionCounting: this.instance ? this.instance.conversionRuleConfig.conversionCounting : 'ALL_PER_INTERACTION',
      adGroupID: adGroupID,
      adGroupName: adGroupName,
      bidAmount: bidAmount,
      scheduleType: 'IMMEDIATELY',
      contents: contents,
      dataAttributes: dataAttributes,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      fileSelectorIndexImage: 0,
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      }
    }
  },
  methods: {
    openChooseFile(name, index, indexImage) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.fileSelectorIndexImage = indexImage
      this.$modal.show('imageModal')
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
        titles: [{
          title: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        brandname: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true,
          existing: false
        }],
        adPreviews: []
      })
    },
    removeContent(index) {
      this.contents.splice(index, 1);
    },
    addTitle(index) {
      this.contents[index].titles.push({
        title: '',
        existing: false
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
        imageUrlState: true,
        existing: false
      })
    },
    removeImage(index, indexImage) {
      this.contents[index].images.splice(indexImage, 1)
    },
    loadPreviewEvent(event, index) {
      this.loadPreview(index)
    },
    validImageHQSizeEvent(event, index) {
      this.validImageSize(this.contents[index].imageUrlHQ, 1200, 627).then(result => {
        this.contents[index].imageUrlHQState = result
      });
    },
    validImageSizeEvent(event, index) {
      this.validImageSize(this.contents[index].imageUrl, 627, 627).then(result => {
        this.contents[index].imageUrlState = result
      });
    },
    loadPreview(index, firstLoad = false) {
      if (!firstLoad && adPreviewCancels.length > 0) {
        for (let i = 0; i < adPreviewCancels.length; i++) {
          adPreviewCancels[i]()
        }
      }
      this.isLoading = true
      this.contents[index].adPreviews = [];

      for (let i = 0; i < this.contents[index].titles.length; i++) {
        for (let y = 0; y < this.contents[index].images.length; y++) {
          axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
            title: this.contents[index].titles[i].title,
            displayUrl: this.contents[index].displayUrl,
            landingUrl: this.contents[index].targetUrl,
            description: this.contents[index].description,
            sponsoredBy: this.contents[index].brandname,
            imageUrlHQ: this.contents[index].images[y].imageUrlHQ,
            imageUrl: this.contents[index].images[y].imageUrl,
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
    },
    selectedAccountChanged() {
      this.getLanguages()
      this.getCountries()
      this.getAdvertisers()
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
        campaignBudgetType: this.campaignBudgetType,
        campaignName: this.campaignName,
        adGroupID: this.adGroupID,
        adGroupName: this.adGroupName,
        bidAmount: this.bidAmount,
        campaignType: this.campaignType,
        campaignLanguage: this.campaignLanguage,
        campaignStrategy: this.campaignStrategy,
        campaignLocation: this.campaignLocation,
        campaignGender: this.campaignGender,
        campaignAge: this.campaignAge,
        campaignDevice: this.campaignDevice,
        campaignConversionCounting: this.campaignConversionCounting,
        scheduleType: this.scheduleType,
        campaignStartDate: this.campaignStartDate,
        campaignEndDate: this.campaignEndDate
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
