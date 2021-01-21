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
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Finish</label>
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
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-sm-8">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Campaign Goal</label>
                  <div class="col-sm-8">
                    <select2 id="goal" name="campaign_campaign_bid_strategy" :options="campaignGoals" v-model="campaignGoal" placeholder="Select campaign goal"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="type" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'ACTIVE' }">
                        <input type="radio" name="type" id="campaignStatus1" autocomplete="off" value="ACTIVE" v-model="campaignStatus"> ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'PAUSED' }">
                        <input type="radio" name="type" id="campaignStatus2" autocomplete="off" value="PAUSED" v-model="campaignStatus"> PAUSED
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="budget" class="col-sm-2 control-label mt-2">Budgets</label>
                  <div class="col-sm-8">
                    <input type="number" name="budget" min="40" class="form-control" v-model="campaignBudget" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="budget" class="col-sm-2 control-label mt-2">Budget Delivery</label>
                  <div class="col-sm-4">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignBudgetDeliveryMethod === 'STANDARD' }">
                        <input type="radio" name="type" id="campaignBudgetDeliveryMethod1" autocomplete="off" value="STANDARD" v-model="campaignBudgetDeliveryMethod"> STANDARD
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignBudgetDeliveryMethod === 'ACCELERATED' }">
                        <input type="radio" name="type" id="campaignBudgetDeliveryMethod2" autocomplete="off" value="ACCELERATED" v-model="campaignBudgetDeliveryMethod"> ACCELERATED
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignBudgetDeliveryMethod === 'UNKNOWN' }">
                        <input type="radio" name="type" id="campaignBudgetDeliveryMethod3" autocomplete="off" value="UNKNOWN" v-model="campaignBudgetDeliveryMethod"> UNKNOWN
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="budget" class="col-sm-2 control-label mt-2">Bid Strategy</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignBidStrategy === 'MANUAL_CPC' }">
                        <input type="radio" name="type" id="campaignBidStrategy1" autocomplete="off" value="MANUAL_CPC" v-model="campaignBidStrategy"> MANUAL CPC
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignBidStrategy === 'MANUAL_CPV' }">
                        <input type="radio" name="type" id="campaignBidStrategy2" autocomplete="off" value="MANUAL_CPV" v-model="campaignBidStrategy"> MANUAL CPV
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignBidStrategy === 'UNKNOWN' }">
                        <input type="radio" name="type" id="campaignBidStrategy3" autocomplete="off" value="UNKNOWN" v-model="campaignBidStrategy"> UNKNOWN
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="bid_strategy" class="col-sm-2 control-label mt-2">Campaign Bid Strategy</label>
                  <div class="col-sm-8">
                    <select2 id="campaign_campaign_bid_strategy" name="campaign_campaign_bid_strategy" :options="campaignCampaignBidStrategies" v-model="campaignCampaignBidStrategy"></select2>
                  </div>
                </div>
                <div class="form-group row" v-if="campaignCampaignBidStrategy == 'MAX_CPC'">
                  <label for="name" class="col-sm-2 control-label mt-2">Max Bid Of Campaign (CPC)</label>
                  <div class="col-sm-8">
                    <input type="text" name="campaign_max_cpc_bid_value" placeholder="Max Bid Of Campaign (CPC)" class="form-control" v-model="campaignMaxCpcBidValue" />
                  </div>
                </div>
                <div class="form-group row" v-if="campaignCampaignBidStrategy == 'MAX_CPV'">
                  <label for="name" class="col-sm-2 control-label mt-2">Max bid of campaign (CPV)</label>
                  <div class="col-sm-8">
                    <input type="text" name="campaign_max_cpv_bid_value" placeholder="Max bid of campaign (CPV)" class="form-control" v-model="campaignMaxCpvBidValue" />
                  </div>
                </div>
                <div class="form-group row" v-if="campaignCampaignBidStrategy == 'MAX_VCPM'">
                  <label for="name" class="col-sm-2 control-label mt-2">Max bid of campaign (vCPM)</label>
                  <div class="col-sm-8">
                    <input type="text" name="campaign_max_vcpm_bid_value" placeholder="Max bid of campaign (vCPM)" class="form-control" v-model="campaignMaxVcpmBidValue" />
                  </div>
                </div>
                <div class="form-group row" v-if="campaignCampaignBidStrategy == 'TARGET_CPA'">
                  <label for="name" class="col-sm-2 control-label mt-2">Target bid of campaign (tCPA)</label>
                  <div class="col-sm-8">
                    <input type="text" name="campaign_Target_cpa_bid_value" placeholder="Target bid of campaign (tCPA)" class="form-control" v-model="campaignTargetCpaBidValue" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
                  <div class="col-sm-3">
                    <VueCtkDateTimePicker id="start_date" v-model="campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true" :minDate="$moment().add(1, 'days').format('YYYY-MM-DD')"></VueCtkDateTimePicker>
                  </div>
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-3">
                    <VueCtkDateTimePicker id="end_date" v-model="campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true" :minDate="$moment().add(1, 'days').format('YYYY-MM-DD')"></VueCtkDateTimePicker>
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
                  <label for="bid_cpc" class="col-sm-2 control-label mt-2">Bid (CPC)</label>
                  <div class="col-sm-8">
                    <input type="number" name="bid_cpc" min="1" class="form-control" v-model="adGroupBidAmount" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="gender" class="col-sm-2 control-label mt-2">Gender</label>
                  <div class="col-sm-8">
                    <select2 id="gender" name="gender" v-model="campaignGenders" :options="genders" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="age" class="col-sm-2 control-label mt-2">Age</label>
                  <div class="col-sm-8">
                    <select2 id="age" name="age" v-model="campaignAges" :options="ages" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select2 name="device" v-model="campaignDevices" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device App</label>
                  <div class="col-sm-8">
                    <select2 name="device" v-model="campaignDeviceApps" :options="deviceApps" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device Os</label>
                  <div class="col-sm-8">
                    <select2 name="device" v-model="campaignDeviceOs" :options="deviceOs" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
              </div>
              <div class="card-body" v-if="currentStep == 2">
                <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in contents" :key="index">
                  <div class="row">
                    <div class="col-sm-7">
                      <div class="form-group row">
                        <label for="title" class="col-sm-4 control-label mt-2">Headline</label>
                        <div class="col-sm-8">
                          <div class="row mb-2" v-for="(headline, indexHeadline) in content.headlines" :key="indexHeadline">
                            <div class="col-sm-8">
                              <input type="text" name="headline" placeholder="Enter a headline" class="form-control" v-model="headline.headline" />
                            </div>
                            <div class="col-sm-4">
                              <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexHeadline)" v-if="indexHeadline > 0"><i class="fa fa-minus"></i></button>
                              <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexHeadline + 1 == content.headlines.length"><i class="fa fa-plus"></i></button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="brand_name" class="col-sm-4 control-label mt-2">Principal</label>
                        <div class="col-sm-8">
                          <input type="text" name="principal" placeholder="Principal" class="form-control" v-model="content.principal" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="description" class="col-sm-4 control-label mt-2">Description</label>
                        <div class="col-sm-8">
                          <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description"></textarea>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                        <div class="col-sm-8 text-center">
                          <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="content.displayUrl" />
                          <small class="text-danger" v-if="content.displayUrl && !validURL(content.displayUrl)">URL is invalid. You might need http/https at the beginning.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                        <div class="col-sm-8 text-center">
                          <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" />
                          <small class="text-danger" v-if="content.targetUrl && !validURL(content.targetUrl)">URL is invalid. You might need http/https at the beginning.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="thumbnail_url" class="col-sm-4 control-label mt-2">Images</label>
                        <div class="col-sm-8">
                          <input type="text" name="image" placeholder="Images" class="form-control" disabled="disabled" v-model="content.imagePath" />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal', index)">Choose Files</button>
                        </div>
                        <div class="col-sm-8 offset-sm-4">
                          <small class="text-danger" v-for="(image, indexImage) in content.images" :key="indexImage">
                            <span class="d-inline-block" v-if="image.image && !image.state">Image {{ image.image }} is invalid. You might need an 1200 x 628 image.</span>
                          </small>
                        </div>
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
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="submitStep1State">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="!submitStep2State">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 3">
              <button type="button" class="btn btn-primary">Finish</button>
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
    submitStep1State() {
      return false
      return !this.selectedAdvertiser || !this.campaignName || !this.campaignGoals.length || !this.campaignBudget || this.campaignBudget <= 0 || !this.campaignStartDate || !this.adGroupBidAmount || this.adGroupBidAmount <= 0 || !this.adGroupName
    },
    submitStep2State() {
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].principal || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        for (let j = 0; j < this.contents[i].headlines.length; j++) {
          if (!this.contents[i].headlines[j].headline) {
            return false
          }
        }

        if (this.contents[i].images.length == 0) {
          return false
        }

        for (let j = 0; j < this.contents[i].images.length; j++) {
          if (!this.contents[i].images[j].image || !this.contents[i].images[j].state) {
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
    this.$root.$on('fm-selected-items', (values) => {
      if (this.openingFileSelector === 'imageModal') {
        this.contents[this.fileSelectorIndex].images = [];
        let paths = []
        for (let i = 0; i < values.length; i++) {
          this.contents[this.fileSelectorIndex].images.push({
            image: values[i].path,
            state: this.validDimensions(values[i].dimensions, 1200, 628),
            existing: false
          })
          paths.push(values[i].path)
        }
        this.contents[this.fileSelectorIndex].imagePath = paths.join(';')
      }
      vm.$modal.hide('imageModal')
    });
    this.currentStep = this.step

    this.getAdvertisers()
  },
  watch: {
    selectedAccount: function(newVal, oldVal) {
      this.getAdvertisers()
    }
  },
  data() {
    let campaignGenders = [],
      campaignAges = [],
      campaignDevices = [],
      campaignDeviceApps = [],
      campaignDeviceOs = [],
      campaignStatus = '',
      adGroupName = '',
      adGroupBidAmount = 1,
      adGroupID = '',
      adGroupBidStrategy = '',

      contents = [{
        id: '',
        headlines: [{
          headline: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        principal: '',
        images: [{
          image: '',
          existing: false
        }],
        imagePath: '',
        adPreviews: []
      }];
    if (this.instance) {
      console.log(this.instance)
      contents = [];
    }

    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      currentStep: 1,
      genders: [{
        id: 'ST_MALE',
        text: 'Male'
      }, {
        id: 'ST_FEMALE',
        text: 'Female'
      }, {
        id: 'ST_UNKNOWN',
        text: 'Unknown'
      }],
      ages: [{
        id: 'GT_RANGE13_14',
        text: '13-14',
      }, {
        id: 'GT_RANGE15_17',
        text: '15-17',
      }, {
        id: 'GT_RANGE18_19',
        text: '18-19',
      }, {
        id: 'GT_RANGE20_21',
        text: '20-21',
      }, {
        id: 'GT_RANGE22_29',
        text: '22-29',
      }, {
        id: 'GT_RANGE30_39',
        text: '30-39',
      }, {
        id: 'GT_RANGE40_49',
        text: '40-49',
      }, {
        id: 'GT_RANGE50_59',
        text: '50-59',
      }, {
        id: 'GT_RANGE60_69',
        text: '60-69',
      }, {
        id: 'GT_RANGE70_UL',
        text: '70 and up',
      }],
      devices: [{
        id: 'DESKTOP',
        text: 'DESKTOP',
      }, {
        id: 'WAP_MOBILE',
        text: 'WAP_MOBILE',
      }, {
        id: 'SMARTPHONE',
        text: 'SMARTPHONE',
      }, {
        id: 'TABLET',
        text: 'TABLET',
      }],
      deviceApps: [{
        id: 'APP',
        text: 'APP',
      }, {
        id: 'WEB',
        text: 'WEB',
      }],
      deviceOs: [{
        id: 'IOS',
        text: 'IOS',
      }, {
        id: 'ANDROID',
        text: 'ANDROID',
      }],
      campaignCampaignBidStrategies: [{
        id: 'AUTO',
        text: 'Auto',
      }, {
        id: 'MAX_VCPM',
        text: 'Max. Bid Value (vCPM)',
      }, {
        id: 'MAX_CPC',
        text: 'Max. Bid Value (CPC)',
      }, {
        id: 'MAX_CPV',
        text: 'Max. Bid Value (CPV)',
      }, {
        id: 'TARGET_CPA',
        text: 'Target Cost Specification (CPA)',
      }, {
        id: 'NONE',
        text: 'No Setting Of Bid Strategy',
      }],
      advertisers: [],
      campaignGoals: [],
      actionName: this.action,
      campaignGoal: this.instance ? this.instance.campaignGoal : '',
      selectedAdvertiser: this.instance ? this.instance.advertiserId : '',
      campaignName: this.instance ? this.instance.campaignName : '',
      campaignBudget: this.instance ? this.instance.budget : '',
      campaignStartDate: this.instance ? this.instance.start_date : this.$moment().add(1, 'days').format('YYYY-MM-DD'),
      campaignEndDate: this.instance ? this.instance.end_date : '',
      campaignCampaignBidStrategy: 'AUTO',
      campaignBidStrategy: '',
      campaignBudgetDeliveryMethod: '',
      campaignMaxCpcBidValue: '',
      campaignMaxCpvBidValue: '',
      campaignMaxVcpmBidValue: '',
      campaignTargetCpaBidValue: '',
      campaignStatus: 'ACTIVE',
      campaignGenders: campaignGenders,
      campaignAges: campaignAges,
      campaignDevices: campaignDevices,
      campaignDeviceApps: campaignDeviceApps,
      campaignDeviceOs: campaignDeviceOs,
      adGroupID: adGroupID,
      adGroupName: adGroupName,
      adGroupBidStrategy: adGroupBidStrategy,
      adGroupBidAmount: adGroupBidAmount,
      contents: contents,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      }
    }
  },
  methods: {
    openChooseFile(name, index) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show('imageModal')
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validDimensions(dimensions, width, height) {
      var dimensions = dimensions.split('x')

      if (dimensions.length == 2) {
        if (dimensions[0].trim() == width && dimensions[1].trim() == height) {
          return true
        }
      }

      return false
    },
    addContent() {
      this.contents.push({
        id: '',
        headlines: [{
          headline: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        principal: '',
        images: [{
          image: '',
          existing: false
        }],
        imagePath: '',
        adPreviews: []
      })
    },
    removeContent(index) {
      this.contents.splice(index, 1);
    },
    addTitle(index) {
      this.contents[index].headlines.push({
        headline: '',
        existing: false
      })
    },
    removeTitle(index, indexHeadline) {
      this.contents[index].headlines.splice(indexHeadline, 1)
    },

    getCampaignGoals() {
      this.isLoading = true
      axios.get(`/general/campaign-goals?provider=${this.selectedProvider}&account=${this.selectedAccount}&advertiser=${this.selectedAdvertiser}`).then(response => {
        this.campaignGoals = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },

    getAdvertisers() {
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.advertisers = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    selectedAdvertiserChange() {
      this.getCampaignGoals()
    },
    submitStep1() {
      const step1Data = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.selectedAdvertiser,
        campaignBudget: this.campaignBudget,
        campaignBudgetDeliveryMethod: this.campaignBudgetDeliveryMethod,
        campaignName: this.campaignName,
        campaignGoal: this.campaignGoal,
        campaignStatus: this.campaignStatus,
        campaignStartDate: this.campaignStartDate,
        campaignEndDate: this.campaignEndDate,
        campaignBidStrategy: this.campaignBidStrategy,
        campaignCampaignBidStrategy: this.campaignCampaignBidStrategy,
        campaignMaxCpcBidValue: this.campaignMaxCpcBidValue,
        campaignMaxCpvBidValue: this.campaignMaxCpvBidValue,
        campaignMaxVcpmBidValue: this.campaignMaxVcpmBidValue,
        campaignTargetCpaBidValue: this.campaignTargetCpaBidValue,
        adGroupID: this.adGroupID,
        adGroupName: this.adGroupName,
        adGroupBidAmount: this.adGroupBidAmount,
        campaignGenders: this.campaignGenders,
        campaignAges: this.campaignAges,
        campaignDevices: this.campaignDevices,
        campaignDeviceApps: this.campaignDeviceApps,
        campaignDeviceOs: this.campaignDeviceOs,
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {
        contents: this.contents
      }
      this.postData = {...this.postData, ...step2Data }

      this.isLoading = true
      let url = '/campaigns';

      if (this.action == 'edit') {
        url += '/update/' + this.instance.instance_id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0]);
        } else {
          this.currentStep = 3
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = '/campaigns';
          });
        }
      }).catch(error => {}).finally(() => {
        this.isLoading = false
      })
    },
  }
}
</script>
