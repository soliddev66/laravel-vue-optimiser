<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>

    <div class="row justify-content-center">
      <div class="col" v-if="currentStep == 1">
        <div class="row">
          <div class="col-md-6 col-lg-4 vendor mt-3" v-for="(vendor, index) in vendors" :key="index">
            <div class="card" v-bind:class="{active: vendor.selected}" @click="vendorClick($event, index)">
              <div class="card-body">
                <img :src="vendor.icon" alt="" width="40">
                <h3 class="pl-3 d-inline">{{ vendor.label }}</h3>

                <div class="row mt-3" v-if="vendor.selected">
                  <div class="col">
                    <select class="form-control" v-model="vendor.selectedAccount">
                      <option value="">Select Account</option>
                      <option :value="account.open_id" v-for="account in vendor.accounts" :key="account.id">{{ account.open_id }}</option>
                    </select>
                  </div>
                </div>

                <div class="row mt-2" v-if="vendor.selected">
                  <div class="col">
                    <select class="form-control" v-model="vendor.selectedAdvertiser" @change="selectedAdvertiserChanged(index)">
                      <option value="">Select Advertiser</option>
                      <option :value="vendor.slug == 'taboola' ? advertiser.account_id : advertiser.id" v-for="advertiser in vendor.advertisers" :key="advertiser.id">{{ vendor.slug == 'taboola' ? advertiser.account_id : advertiser.id }} - {{ advertiser.advertiserName || advertiser.name }}</option>
                    </select>
                  </div>
                </div>

                <div v-if="vendor.selected">
                  <fieldset class="mb-2 mt-2 p-2 rounded border" v-for="(campaign, campaignIndex) in vendor.campaigns" :key="campaignIndex">
                    <div class="row">
                      <div class="col">
                        <select2 v-model="campaign.id" :options="vendor.campaignSelections" :settings="{ templateSelection: formatState, templateResult: formatState, placeholder: 'Select Campaign' }" @change="campaignSelected(index, campaignIndex)" />
                      </div>
                    </div>
                    <div class="row mt-2" v-if="[1, 3, 5].includes(vendor.id)">
                      <div class="col">
                        <select2 v-model="campaign.adGroups" :options="campaign.adGroupSelections" :settings="{ multiple: true, placeholder: 'Select Ad Groups' }" />
                      </div>
                    </div>
                    <div class="row mt-2" v-if="campaignIndex > 0">
                      <div class="col">
                        <button type="button" class="btn btn-sm btn-light" @click.prevent="removeCampaign(index, campaignIndex)"><i class="fa fa-minus"></i></button>
                      </div>
                    </div>
                  </fieldset>

                  <div class="row mt-2">
                    <div class="col">
                      <button type="button" class="btn btn-sm btn-light" @click="addCampaign(index)">ADD</button>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col d-none" :class="{ 'd-block': currentStep != 1 }">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">General Information</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Vendors</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 4 }">Ad Content</label>
          </div>
          <div class="card-body">
            <div v-if="currentStep == 2">
              <div class="form-group row">
                <label for="name" class="col-sm-2 control-label mt-2">Campaign Name</label>
                <div class="col-sm-8">
                  <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                </div>
              </div>
            </div>

            <div class="d-none" :class="{ 'd-block': currentStep == 3 }">
              <div v-for="vendor in vendors" :key="vendor.id">
                <div class="d-none" :class="{ 'd-block': vendor.selected && !vendor.generated && currentVendor && currentVendor.slug == vendor.slug }" v-if="vendor.selected">
                  <component :is="vendor.slug" :vendor="vendor" :ref="vendor.slug" />
                </div>
              </div>
            </div>

            <div v-if="currentStep == 4">
              <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in contents" :key="index">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="form-group row">
                      <label for="title" class="col-sm-4 control-label mt-2">Title Set</label>
                      <div class="col-sm-8">
                        <button type="button" class="btn btn-primary" @click="loadCreativeSet('title', index)">Load Set</button>
                        <span v-if="content.titleSet.id" class="selected-set">{{ content.titleSet.name }}<span class="close" @click="removeTitleSet(index)"><i class="fas fa-times"></i></span></span>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name / Principal</label>
                      <div class="col-sm-8">
                        <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="content.brandname" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="description" class="col-sm-4 control-label mt-2">Description Set</label>
                      <div class="col-sm-8">
                        <button class="btn btn-primary" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('description', index)">Load from Sets</button>
                        <span v-if="content.descriptionSet.id" class="selected-set">{{ content.descriptionSet.name }}<span class="close" @click="removeDescriptionSet(index)"><i class="fas fa-times"></i></span></span>
                      </div>
                    </div>

                    <div class="form-group row">
                      <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                      <div class="col-sm-8">
                        <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="content.displayUrl" />
                        <small class="text-danger" v-if="content.displayUrl && !validURL(content.displayUrl)">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                      <div class="col-sm-8">
                        <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" />
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
                      <div class="form-group row">
                        <label for="image_hq_url" class="col-sm-4 control-label mt-2">Image Set</label>
                        <div class="col">
                          <button class="btn btn-primary" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                          <span v-if="content.imageSet.id" class="selected-set">{{ content.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                    </div>

                    <div v-if="content.adType == 'VIDEO'">
                      <div class="form-group row">
                        <label for="image_hq_url" class="col-sm-4 control-label mt-2">Video Set</label>
                        <div class="col">
                          <button class="btn btn-primary" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('video', index)">Load from Sets</button>
                          <span v-if="content.videoSet.id" class="selected-set">{{ content.videoSet.name }}<span class="close" @click="removeVideoSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                    </div>

                    <div class="form-group row">
                      <label for="tweet_nullcast" class="col-sm-4 control-label mt-2">Tweet Nullcast</label>
                      <div class="col-lg-4 col-xl-3">
                        <div class="btn-group btn-group-toggle">
                          <label class="btn bg-olive" :class="{ active: content.tweetNullcast }">
                            <input type="radio" name="tweet_nullcast" id="tweetNullcast1" autocomplete="off" :value="true" v-model="content.tweetNullcast">TRUE
                          </label>
                          <label class="btn bg-olive" :class="{ active: !content.tweetNullcast }">
                            <input type="radio" name="tweet_nullcast" id="tweetNullcast2" autocomplete="off" :value="false" v-model="content.tweetNullcast">FALSE
                          </label>
                        </div>
                      </div>
                    </div>

                    <div class="form-group row">
                      <label for="card_name" class="col-sm-4 control-label mt-2">Card Name</label>
                      <div class="col-lg-10 col-xl-8">
                        <input type="text" name="card_name" placeholder="Enter a name" class="form-control" v-model="content.name" />
                      </div>
                    </div>

                    <div class="form-group row">
                      <label for="card_website_title" class="col-sm-4 control-label mt-2">Card Website Title</label>
                      <div class="col-lg-10 col-xl-8">
                        <input type="text" name="card_website_title" placeholder="Enter website title" class="form-control" v-model="content.websiteTitle" />
                      </div>
                    </div>
                  </div>

                  <div class="col-sm-5">
                    <h1>Preview</h1>
                    <section v-for="(title, indexTitle) in content.titleSet.sets" :key="indexTitle">
                      <section v-for="(image, indexImage) in content.imageSet.sets" :key="indexImage">
                        <div class="row no-gutters mb-2" v-if="image.image">
                          <div class="col-sm-5" v-if="content.adType == 'IMAGE'">
                            <img :src="storageUrl + '/storage/images/' + image.image" class="card-img-top h-100">
                          </div>
                          <div class="col-sm-7">
                            <div class="card-body">
                              <h3 class="card-title">{{ title.title }}</h3>
                              <h6 class="card-text mt-5"><i>{{ content.brandname }}</i></h6>
                              <p>{{ content.descriptionSet.sets[0].description || '' }}</p>
                            </div>
                          </div>
                        </div>
                      </section>
                    </section>
                  </div>
                </div>
                <div class="row" v-if="index > 0">
                  <div class="col text-right">
                    <button class="btn btn-warning btn-sm" @click.prevent="removeContent(index)">Remove</button>
                  </div>
                </div>
              </fieldset>
              <button class="btn btn-primary btn-sm" @click.prevent="addContent()">Add New</button>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end" v-if="currentStep != 1">
            <div class="d-flex justify-content-start flex-grow-1" v-if="[2, 4].includes(currentStep)">
              <button type="button" class="btn btn-primary" @click.prevent="backStep()">Back</button>
            </div>

            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep === 3">
              <div v-for="vendor in vendors" :key="vendor.id">
                <button type="button" class="btn btn-primary" v-if="vendor.selected && !vendor.generated && currentVendor && currentVendor.slug == vendor.slug" @click.prevent="backVendor(vendor)">Back</button>
              </div>
            </div>

            <div class="d-flex justify-content-end" v-if="currentStep == 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="!submitStep2State">Next</button>
            </div>

            <div v-if="currentStep === 3">
              <div v-for="vendor in vendors" :key="vendor.id">
                <div class="d-flex justify-content-end" v-if="vendor.selected &&!vendor.generated && currentVendor && currentVendor.slug == vendor.slug">
                  <button type="button" class="btn btn-primary" @click.prevent="submitVendor(vendor)" :disabled="!$refs[vendor.slug][0].vendorState">Next</button>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end" v-if="currentStep == 4">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep4" :disabled="!submitStep4State">Finish</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col">
        <div class="d-flex justify-content-end mb-5" v-if="currentStep == 1">
          <button type="button" class="btn btn-vendor mr-5" :disabled="!submitStep1State" @click="submitStep1">
            Next <i class="fas fa-long-arrow-alt-right"></i>
          </button>
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
  </div>
</template>

<script>
import {
  yahoo,
  outbrain,
  yahoojp,
  twitter,
  taboola
} from './ad-vendors/creative-sets'

import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import Echo from 'laravel-echo'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    userId: {
      type: Number,
      default: 0
    },
    step: {
      type: Number,
      default: 1
    }
  },
  components: {
    Loading,
    Select2,
    yahoo,
    outbrain,
    yahoojp,
    twitter,
    taboola
  },
  computed: {
    submitStep1State() {
      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected) {
          return true
        }
      }

      return false
    },

    submitStep2State() {
      return this.campaignName
    },

    submitStep4State() {
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].brandname || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl) || !this.contents[i].titleSet.id || (this.contents[i].adType == 'IMAGE' && !this.contents[i].imageSet.id) || (this.contents[i].adType == 'VIDEO' && !this.contents[i].videoSet.id) || !this.contents[i].descriptionSet.id) {
          return false
        }
      }

      return true
    }
  },
  mounted() {
    window.Pusher = require('pusher-js')

    window.Echo = new Echo({
      broadcaster: 'pusher',
      key: process.env.MIX_PUSHER_APP_KEY,
      cluster: process.env.MIX_PUSHER_APP_CLUSTER
    })
  },
  watch: {

  },
  data() {
    let vendors = []

    for (let i = 0; i < this.providers.length; i++) {
      let vendor = {
        id: this.providers[i].id,
        slug: this.providers[i].slug,
        label: this.providers[i].label,
        icon: this.providers[i].icon,
        accounts: [],
        advertisers: [],
        selectedAccount: '',
        selectedAdvertiser: '',
        selected: false,
        loaded: false,
        generated: false,
        campaigns: [{
          id: null,
          adGroups: []
        }]
      }

      if (this.providers[i].slug == 'yahoo') {
        Object.assign(vendor, {
          campaignType: 'NATIVE',
          campaignLanguage: 'en',
          campaignStrategy: 'OPT_CLICK',
          campaignObjective: 'VISIT_WEB',
          campaignStartDate: this.$moment().format('YYYY-MM-DD'),
          campaignBudgetType: 'DAILY',
          campaignConversionCounting: 'ALL_PER_INTERACTION',
          scheduleType: 'IMMEDIATELY',
          campaignLocation: [],
          supportedSiteCollections: []
        })
      } else if (this.providers[i].slug == 'outbrain') {
        Object.assign(vendor, {
          campaignBudgetType: 'DAILY',
          campaignPacing: 'SPEND_ASAP',
          campaginPlatform: ['DESKTOP', 'MOBILE', 'TABLET'],
          campaignOperatingSystem: ['Ios', 'MacOs', 'Android', 'Windows'],
          campaignBrowser: ['Safari', 'Opera', 'Chrome', 'UCBrowser', 'InApp', 'Samsung', 'Firefox', 'InternetExplorer', 'Edge'],
          campaignExcludeAdBlockUsers: true,
          campaignStartDate: this.$moment().format('YYYY-MM-DD'),
          campaignObjective: 'Awareness',
          campaignBudget: 20
        })
      } else if (this.providers[i].slug == 'twitter') {
        Object.assign(vendor, {
          selectedFundingInstrument: '',
          campaignStartTime: this.$moment().format('YYYY-MM-DD'),
          campaignStatus: 'PAUSED',
          adGroupObjective: 'APP_ENGAGEMENTS',
          adGroupStatus: 'PAUSED',
          adGroupBidUnit: 'LINK_CLICK',
          adGroupChargeBy: 'LINK_CLICK'
        })
      } else if (this.providers[i].slug == 'taboola') {
        Object.assign(vendor, {
          campaignSpendingLimitModel: 'MONTHLY',
          campaignMarketingObjective: 'DRIVE_WEBSITE_TRAFFIC',
          campaignStartDate: this.$moment().format('YYYY-MM-DD'),
          campaignIsActive: false
        })
      } else if (this.providers[i].slug == 'yahoojp') {
        Object.assign(vendor, {
          campaignCampaignBidStrategy: 'AUTO',
          campaignStatus: 'ACTIVE',
          campaignStartDate: this.$moment().format('YYYY-MM-DD')
        })
      }

      vendors.push(vendor)
    }

    return {
      isLoading: false,
      fullPage: true,
      currentStep: this.step,
      currentVendor: null,
      vendors: vendors,
      storageUrl: process.env.MIX_APP_URL,
      contents: [{
        adType: 'IMAGE',
        titleSet: '',
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        brandname: '',
        imageSet: '',
        videoSet: '',
        tweetNullcast: false,
        cardName: '',
        websiteTitle: ''
      }],
      campaignName: '',
      setType: 'image',
      adSelectorIndex: 0,
    }
  },
  methods: {
    formatState(state) {
      if (!state.id) {
        return state.text
      }
      var $state = $(
        '<span class="campaign-item"><img src="' + state.icon + '" width="20px" height="20px" /> ' + state.text + '</span>'
      );
      return $state
    },

    getAccounts(vendor) {
      this.isLoading = true
      return axios.get(`/account/accounts?provider=${vendor.slug}`).then(response => {
        vendor.accounts = response.data
        vendor.selectedAccount = vendor.accounts[0].open_id
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },

    getAdvertisers(vendor) {
      this.isLoading = true
      return axios.get(`/account/advertisers?provider=${vendor.slug}&account=${encodeURIComponent(vendor.selectedAccount)}`).then(response => {
        vendor.advertisers = response.data
        vendor.selectedAdvertiser = vendor.slug == 'taboola' ? vendor.advertisers[0].account_id : vendor.advertisers[0].id
      }).finally(() => {
        this.isLoading = false
      })
    },

    selectedAdvertiserChanged(index) {
      this.vendors[index].loaded = false

      this.loadVendorCampaigns(index)
    },

    loadVendorCampaigns(index) {
      this.isLoading = true
      axios.get('/campaigns/user-campaigns?provider=' + this.vendors[index].id).then(response => {
        this.vendors[index].campaignSelections = response.data.campaigns.map(campaign => {
          return {
            id: campaign.id,
            text: campaign.name,
            icon: campaign.icon
          }
        })
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },

    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g
      return !!pattern.test(str)
    },

    vendorClick(event, index) {
      let abandonClasses = [
        'form-control',
        'select2-selection__rendered',
        'select2-selection select2-selection--single',
        'select2-selection__placeholder',
        'select2-selection__choice',
        'select2-selection__choice__remove',
        'select2-search__field',
        'btn btn-sm btn-light',
        'campaign-item',
        'fa fa-minus'
      ]

      if (abandonClasses.includes(event.target.className)) {
        return
      }

      if (this.vendors[index].accounts.length == 0) {
        this.getAccounts(this.vendors[index]).then(() => {
          this.vendors[index].selected = !this.vendors[index].selected

          this.getAdvertisers(this.vendors[index]).then(() => {
            this.loadVendorCampaigns(index)
          })
        })
      } else {
        this.vendors[index].selected = !this.vendors[index].selected
      }

      this.vendors[index].loaded = false
    },

    submitStep1() {
      this.currentStep = 4

      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected) {
          this.vendors[i].generated = false
          for (let j = 0; j < this.vendors[i].campaigns.length; j++) {
            if (([1, 3, 5].includes(this.vendors[i].id) && this.vendors[i].campaigns[j].adGroups.length > 0) || (![1, 3, 5].includes(this.vendors[i].id) && this.vendors[i].campaigns[j].id)) {
              this.vendors[i].generated = true
              break
            }
          }
        }
      }

      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected && !this.vendors[i].generated) {
          this.currentStep = 2
          return
        }
      }
    },

    submitStep2() {
      this.currentStep = 3
      this.currentVendor = null

      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected && !this.vendors[i].generated) {
          this.currentVendor = this.vendors[i]
          break
        }
      }

      if (this.currentVendor) {
        if (!this.currentVendor.loaded) {
          this.$refs[this.currentVendor.slug][0].preparingData()
          this.currentVendor.loaded = true
        }
      } else {
        this.currentStep = 4
      }
    },

    backStep() {
      if (this.currentStep == 2) {
        this.currentStep = 1
      } else {
        for (let i = 0; i < this.vendors.length; i++) {
          if (this.vendors[i].selected && !this.vendors[i].generated) {
            this.currentStep = 3

            return
          }
        }

        this.currentStep = 1
      }
    },

    backVendor(vendor) {
      let found = false

      for (let i = this.vendors.length - 1; i >= 0; i--) {
        if (this.vendors[i].slug == vendor.slug) {
          found = true
          continue
        }

        if (found && this.vendors[i].selected && !this.vendors[i].generated) {
          this.currentVendor = this.vendors[i]
          return
        }
      }

      this.currentStep = 2
    },

    submitVendor(vendor) {
      let found = false

      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].slug == vendor.slug) {
          found = true
          continue
        }

        if (found && this.vendors[i].selected && !this.vendors[i].generated) {
          this.currentVendor = this.vendors[i]

          if (!this.currentVendor.loaded) {
            this.$refs[this.currentVendor.slug][0].preparingData()
            this.currentVendor.loaded = true
          }

          return
        }
      }

      if (!this.currentVendor.loaded) {
        this.$refs[this.currentVendor.slug][0].preparingData()
        this.currentVendor.loaded = true
      }

      this.currentStep = 4
    },

    loadCreativeSet(type, index) {
      this.setType = type
      this.adSelectorIndex = index
      $('#creative-set-modal').modal('show')
    },

    selectCreativeSet(set) {
      if (this.setType == 'title') {
        this.contents[this.adSelectorIndex].titleSet = set
        this.loadTitleSets(this.adSelectorIndex)
      }
      if (this.setType == 'image') {
        this.contents[this.adSelectorIndex].imageSet = set
        this.loadImageSets(this.adSelectorIndex)
      }
      if (this.setType == 'video') {
        this.contents[this.adSelectorIndex].videoSet = set
        this.loadVideoSets(this.adSelectorIndex)
      }
      if (this.setType == 'description') {
        this.contents[this.adSelectorIndex].descriptionSet = set
        this.loadDescriptionSets(this.adSelectorIndex)
      }

      $('#creative-set-modal').modal('hide')
    },

    loadTitleSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/title-sets/${this.contents[index].titleSet.id}`).then(response => {
        this.contents[index].titleSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      })
    },

    loadImageSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/image-sets/${this.contents[index].imageSet.id}`).then(response => {
        this.contents[index].imageSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      })
    },

    loadVideoSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/video-sets/${this.contents[index].videoSet.id}`).then(response => {
        this.contents[index].videoSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      })
    },

    loadDescriptionSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/description-sets/${this.contents[index].descriptionSet.id}`).then(response => {
        this.contents[index].descriptionSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      })
    },

    removeImageSet(index) {
      this.contents[index].imageSet = ''
    },

    removeVideoSet(index) {
      this.contents[index].videoSet = ''
    },

    removeTitleSet(index) {
      this.contents[index].titleSet = ''
    },

    removeDescriptionSet(index) {
      this.contents[index].descriptionSet = ''
    },

    addContent() {
      this.contents.push({
        adType: 'IMAGE',
        titleSet: '',
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        brandname: '',
        imageSet: '',
        videoSet: '',
        tweetNullcast: false,
        cardName: '',
        websiteTitle: ''
      })
    },

    removeContent(index) {
      this.contents.splice(index, 1)
    },

    addCampaign(index) {
      this.vendors[index].campaigns.push({ id: null, adGroups: [] })
    },

    removeCampaign(index, campaignIndex) {
      this.vendors[index].campaigns.splice(campaignIndex, 1)
    },

    campaignSelected(index, campaignIndex) {
      if ([1, 3, 5].includes(this.vendors[index].id)) {
        this.loadAdGroups(this.vendors[index].campaigns[campaignIndex])
      }
    },

    loadAdGroups(campaign) {
      this.isLoading = true
      axios.get('/campaigns/' + campaign.id + '/ad-groups/selection')
        .then((response) => {
          campaign.adGroupSelections = response.data
        })
        .catch((err) => {
          alert(err)
        }).finally(() => {
          this.isLoading = false
        })
    },

    submitStep4() {
      this.isLoading = true

      let totalVendor = 0, processedTime = 0

      for (let i = 0; i< this.vendors.length; i++) {
        if (this.vendors[i].selected) {
          totalVendor ++
        }
      }

      window.Echo.private('campaign.' + this.userId).listen('CampaignVendorCreated', response => {
        processedTime ++

        if (!response.data.success) {
          alert(response.data.errors[0])
        }

        if (processedTime == totalVendor) {
          this.isLoading = false

          this.$dialog.alert('All vendors have been processed!').then(() => {
            window.location = '/campaigns'
          })
        }
      })

      axios.post('/campaigns/store-campaign-vendors', {
        campaignName: this.campaignName,
        vendors: this.vendors,
        contents: this.contents
      }).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        }
      }).catch(error => console.log(error)).finally(() => {

      })
    },
  }
}
</script>

<style>
.btn-vendor {
  color: #4969ed;
  min-height: 50px;
  border: none;
}

.btn-vendor:hover {
  color: #344eb4;
}

.vendor h3 {
  font-size: 1.3em;
}

.vendor .card {
  border: none;
  cursor: pointer;
  transition: all 0.3s linear;
  overflow: hidden;
}

.vendor .card-body {
  padding: 0.5rem;
}

.vendor .card.active, .vendor .card:hover {
  background: #607cef;
  color: #fff;
}
</style>