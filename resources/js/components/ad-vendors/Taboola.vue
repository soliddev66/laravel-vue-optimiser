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
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">Add Campaign Items</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Waiting for approval</label>
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
                      <option :value="advertiser.id" v-for="advertiser in advertisers" :key="advertiser.account_id">{{ advertiser.name }}</option>
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
                  <label for="branding_text" class="col-sm-2 control-label mt-2">Branding Name</label>
                  <div class="col-sm-8">
                    <input type="text" name="branding_text" placeholder="Enter branding text" class="form-control" v-model="campaignBrandText" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="is_active" class="col-sm-2 control-label mt-2">Active</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignIsActive }">
                        <input type="radio" name="is_active" id="is_active_1" autocomplete="off" value="true" v-model="campaignIsActive">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !campaignIsActive }">
                        <input type="radio" name="is_active" id="is_active_2" autocomplete="off" value="false" v-model="campaignIsActive">FALSE
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="cpc" class="col-sm-2 control-label mt-2">Cost per Click</label>
                  <div class="col-sm-8">
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                      </div>
                      <input type="number" name="cpc" class="form-control" v-model="campaignCPC" id="cpc" />
                      <div class="input-group-append">
                        <span class="input-group-text">per click</span>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="spending_limit" class="col-sm-2 control-label mt-2">Budget</label>
                  <div class="col-sm-4">
                    <input type="number" name="spending_limit" class="form-control" v-model="campaignSpendingLimit" id="spending_limit" />
                  </div>
                  <div class="col-sm-4">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignSpendingLimitModel === 'MONTHLY' }">
                        <input type="radio" name="type" id="spending_limit_model_2" autocomplete="off" value="MONTHLY" v-model="campaignSpendingLimitModel"> Per Month
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignSpendingLimitModel === 'ENTIRE' }">
                        <input type="radio" name="type" id="spending_limit_model_3" autocomplete="off" value="ENTIRE" v-model="campaignSpendingLimitModel"> In Total
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="marketing_objective" class="col-sm-2 control-label mt-2">Marketing Objective</label>
                  <div class="col-sm-8">
                    <select2 id="marketing_objective" v-model="campaignMarketingObjective" :options="campaignMarketingObjectives" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="country_targeting" class="col-sm-2 control-label mt-2">Location</label>
                  <div class="col-sm-8">
                    <select2 id="country_targeting" name="country_targeting" v-model="campaignCountryTargeting" :options="countries" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="platform_targeting" class="col-sm-2 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select2 name="platform_targeting" v-model="campaignPlatformTargeting" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
                  <div class="col-sm-3">
                    <VueCtkDateTimePicker id="start_date" v-model="campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-3">
                    <VueCtkDateTimePicker id="end_date" v-model="campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                </div>
              </div>
              <div v-if="currentStep == 2">
                <fieldset class="mb-3 p-3 rounded border" v-for="(campaignItem, index) in campaignItems" :key="index">
                  <div class="form-group row">
                    <label for="url" class="col-sm-2 control-label mt-2">Url</label>
                    <div class="col-sm-8">
                      <input type="text" name="url" placeholder="Enter a url" class="form-control" v-model="campaignItem.url" />
                      <small class="text-danger" v-if="campaignItem.url && !validURL(campaignItem.url)">URL is invalid. You might need http/https at the beginning.</small>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="url" class="col-sm-2 control-label mt-2">Title</label>
                    <div class="col-sm-8">
                      <div class="row mb-2" v-for="(title, indexTitle) in campaignItem.titles" :key="indexTitle">
                        <div class="col-sm-8">
                          <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" :disabled="campaignItem.titleSet.id" />
                        </div>
                        <div class="col-sm-4">
                          <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle)" v-if="indexTitle > 0" :disabled="campaignItem.titleSet.id"><i class="fa fa-minus"></i></button>
                          <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == campaignItem.titles.length"><i class="fa fa-plus"></i></button>
                          <button type="button" class="btn btn-primary" v-if="indexTitle == 0" @click="loadCreativeSet('title', index)"><i class="far fa-folder-open"></i></button>
                        </div>
                      </div>
                      <div class="row" v-if="campaignItem.titleSet.id">
                        <div class="col">
                          <span class="selected-set">{{ campaignItem.titleSet.name }}<span class="close" @click="removeTitleSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label for="ad_type" class="col-sm-2 control-label mt-2">Ad Type</label>
                    <div class="col-sm-8">
                      <div class="btn-group btn-group-toggle">
                        <label class="btn bg-olive" :class="{ active: campaignItem.adType === 'IMAGE' }">
                          <input type="radio" name="ad_type" autocomplete="off" value="IMAGE" v-model="campaignItem.adType"> IMAGE
                        </label>
                        <label class="btn bg-olive" :class="{ active: campaignItem.adType === 'VIDEO' }">
                          <input type="radio" name="ad_type" autocomplete="off" value="VIDEO" v-model="campaignItem.adType"> VIDEO
                        </label>
                      </div>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label for="description" class="col-sm-2 control-label mt-2">Description</label>
                    <div class="col-sm-8">
                      <textarea class="form-control" rows="3" placeholder="Enter description" v-model="campaignItem.description" :disabled="campaignItem.descriptionSet.id"></textarea>
                      <div class="row mt-2">
                        <div class="col">
                          <span v-if="campaignItem.descriptionSet.id" class="selected-set">{{ campaignItem.descriptionSet.name }}<span class="close" @click="removeDescriptionSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                      <button class="btn btn-primary btn-sm mt-2" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('description', index)">Load from Sets</button>
                    </div>
                  </div>
                  <div v-if="campaignItem.adType == 'IMAGE'">
                    <div class="form-group row">
                      <label for="thumbnail_url" class="col-sm-2 control-label mt-2">Thumbnail Images</label>
                      <div class="col-sm-8">
                        <input type="text" name="thumbnail_url" placeholder="Thumbnail Images" class="form-control" disabled="disabled" v-model="campaignItem.imagePath" />

                        <div class="row mt-2 mb-2">
                          <div class="col">
                            <span v-if="campaignItem.imageSet.id" class="selected-set">{{ campaignItem.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                          </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('thumbnailUrl', index)" :disabled="campaignItem.imageSet.id">Choose Files</button>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                      </div>
                    </div>
                  </div>

                  <div v-if="campaignItem.adType == 'VIDEO'">
                    <fieldset class="mb-3 p-3 rounded border" v-for="(video, videoIndex) in campaignItem.videos" :key="videoIndex">
                      <div class="form-group row">
                        <label for="video_url" class="col-sm-2 control-label mt-2">Video URL</label>
                        <div class="col-sm-8">
                          <input type="text" name="video_url" placeholder="Enter video URL" class="form-control" v-model="video.videoUrl" :disabled="content.videoSet.id" />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoUrl', index, videoIndex)" :disabled="content.videoSet.id">Choose File</button>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="image_url" class="col-sm-2 control-label mt-2">Image URL</label>
                        <div class="col-sm-8">
                          <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="video.imageUrl" :disabled="content.videoSet.id" />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageUrl', index, videoIndex)" :disabled="content.videoSet.id">Choose File</button>
                        </div>
                      </div>
                      <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeVideo(index, videoIndex)" v-if="videoIndex > 0">Remove Video</button>
                    </fieldset>
                    <div class="row mt-2 mb-2">
                      <div class="col">
                        <span v-if="campaignItem.videoSet.id" class="selected-set">{{ campaignItem.videoSet.name }}<span class="close" @click="removeVideoSet(index)"><i class="fas fa-times"></i></span></span>
                      </div>
                    </div>
                    <button class="btn btn-primary btn-sm" @click.prevent="addVideo(index)" :disabled="campaignItem.id || campaignItem.videoSet.id">Add Video</button>
                    <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('video', index)">Load from Sets</button>
                  </div>

                  <div class="row" v-if="index > 0 && action == 'create'">
                    <div class="col text-right">
                      <button class="btn btn-warning btn-sm" @click.prevent="removeCampaignItem(index)">Remove</button>
                    </div>
                  </div>
                </fieldset>
                <button class="btn btn-primary btn-sm" @click.prevent="addCampaignItem()">Add New</button>
              </div>
            </form>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 4 && currentStep > 1">
            <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
          </div>
          <div class="d-flex justify-content-end" v-if="currentStep === 1">
            <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="submitStep1State">Next</button>
          </div>
          <div class="d-flex justify-content-end" v-if="currentStep === 2">
            <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="!submitStep2State">Next</button>
          </div>
          <div class="d-flex justify-content-end" v-if="currentStep === 3">
            <button type="button" class="btn btn-primary" @click.prevent="submitStep3">Finish</button>
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
          selectionType: 'multiple'
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
    submitStep1State() {
      return !this.selectedAdvertiser || !this.campaignName || !this.campaignBrandText || !this.campaignCPC || !this.campaignSpendingLimit
    },
    submitStep2State() {
      for (let i = 0; i < this.campaignItems.length; i++) {
        if (!this.campaignItems[i].url) {
          return false
        }
      }

      return true
    }
  },
  mounted() {
    this.currentStep = this.step

    this.getAdvertisers()
    this.getCountries()

    let vm = this
    this.$root.$on('fm-selected-items', (values) => {

      if (this.openingFileSelector === 'thumbnailUrl') {
        this.campaignItems[this.fileSelectorIndex].images = [];
        let paths = []
        for (let i = 0; i < values.length; i++) {
          this.campaignItems[this.fileSelectorIndex].images.push({
            image: process.env.MIX_APP_URL + '/storage/images/' + values[i].path,
            existing: false
          })
          paths.push(values[i].path)
        }
        this.campaignItems[this.fileSelectorIndex].imagePath = paths.join(';')
      } else if (this.openingFileSelector === 'videoUrl') {
        this.campaignItems[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoUrl = process.env.MIX_APP_URL + '/storage/images/' + values[0].path
      } else if (this.openingFileSelector === 'imageUrl') {
        this.campaignItems[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].imageUrl = process.env.MIX_APP_URL + '/storage/images/' + values[0].path
      }


      vm.$modal.hide('imageModal')
    });
  },
  watch: {

  },
  data() {
    let campaignItems = [{
      url: '',
      adType: 'IMAGE',
      titleSet: '',
      titles: [{
        title: '',
        existing: false
      }],
      descriptionSet: '',
      description: '',
      imageSet: '',
      images: [{
        image: '',
        existing: false
      }],
      videoSet: '',
      videos: [{
        videoUrl: '',
        imageUrl: '',
        existing: false
      }],
      imagePath: ''
    }]

    if (this.instance) {
      campaignItems = []
      for (let i = 0; i < this.instance.items.length; i++) {
        let campaignItem = {
          id: this.instance.items[i].id,
          url: this.instance.items[i].url,
          titleSet: this.instance.items[i]['titleSet'] || '',
          titles: [{
            title: this.instance.items[i].title,
            existing: true
          }],
          descriptionSet: this.instance.items[i]['descriptionSet'] || '',
          description: this.instance.items[i].description,

        }

        if (this.instance.items[i].type == 1) {
          campaignItem.adType = 'IMAGE'

          campaignItem.imageSet = this.instance.items[i]['imageSet'] || ''
          campaignItem.images = [{
            image: this.instance.items[i].thumbnail_url,
            existing: true
          }]

          campaignItem.imagePath = this.instance.items[i].thumbnail_url
        } else {
          campaignItem.adType = 'VIDEO'

          campaignItem.videoSet = this.instance.ads[i]['videoSet'] || ''
          campaignItem.videos = [{
            videoUrl: this.instance.items[i].video_url,
            imageUrl: this.instance.items[i].fallback_url,
            existing: true
          }]
        }

        campaignItems.push(campaignItem)
      }
    }

    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      currentStep: 1,
      actionName: this.action,
      advertisers: [],
      selectedAdvertiser: this.instance ? this.instance.advertiser_id : '',
      campaignName: this.instance ? this.instance.name : '',
      campaignBrandText: this.instance ? this.instance.branding_text : '',
      campaignCPC: this.instance ? this.instance.cpc : '',
      campaignSpendingLimit: this.instance ? this.instance.spending_limit : '',
      campaignSpendingLimitModel: this.instance ? this.instance.spending_limit_model : 'MONTHLY',
      campaignMarketingObjective: this.instance ? this.instance.marketing_objective : 'DRIVE_WEBSITE_TRAFFIC',
      campaignMarketingObjectives: [
        'BRAND_AWARENESS',
        'LEADS_GENERATION',
        'ONLINE_PURCHASES',
        'DRIVE_WEBSITE_TRAFFIC'
      ],
      campaignCountryTargeting: this.instance && this.instance.country_targeting.type == 'INCLUDE' ? this.instance.country_targeting.value : [],
      countries: [],
      campaignStartDate: this.instance ? this.instance.start_date : this.$moment().format('YYYY-MM-DD'),
      campaignEndDate: this.instance && this.instance.end_date != '9999-12-31' ? this.instance.end_date : '',
      campaignPlatformTargeting: this.instance && this.instance.platform_targeting.type == 'INCLUDE' ? this.instance.platform_targeting.value : [],
      devices: [{
        id: '',
        text: 'All',
      }, {
        id: 'PHON',
        text: 'SMARTPHONE',
      }, {
        id: 'TBLT',
        text: 'TABLET',
      }, {
        id: 'DESK',
        text: 'DESKTOP',
      }],
      campaignIsActive: this.instance ? this.instance.is_active : false,
      campaignItems: campaignItems,
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
    openChooseFile(name, index, videoIndex) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index

      this.fileSelectorVideoIndex = videoIndex

      if (name == 'thumbnailUrl') {
        this.$root.$store.commit('fm/setSelectionType', 'multiple')
      } else {
        this.$root.$store.commit('fm/setSelectionType', 'single')
      }

      this.$modal.show('imageModal')
    },

    getAdvertisers() {
      this.advertisers = []
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.advertisers = response.data.map(item => {
          return {
            id: item.account_id,
            name: item.name
          }
        })
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
              id: country.name,
              text: country.value
            }
          })
        }
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },

    loadCreativeSet(type, index) {
      this.setType = type
      this.adSelectorIndex = index
      $('#creative-set-modal').modal('show')
    },

    selectCreativeSet(set) {
      if (this.setType == 'title') {
        this.campaignItems[this.adSelectorIndex].titleSet = set
      }
      if (this.setType == 'image') {
        this.campaignItems[this.adSelectorIndex].imageSet = set
      }
      if (this.setType == 'video') {
        this.campaignItems[this.adSelectorIndex].videoSet = set
      }
      if (this.setType == 'description') {
        this.campaignItems[this.adSelectorIndex].descriptionSet = set
      }

      $('#creative-set-modal').modal('hide')
    },

    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g
      return !!pattern.test(str)
    },

    addCampaignItem() {
      this.campaignItems.push({
        url: '',
        adType: 'IMAGE',
        titleSet: '',
        titles: [{
          title: '',
          existing: false
        }],
        description: '',
        descriptionSet: '',
        imageSet: '',
        images: [{
          image: '',
          existing: false
        }],
        videoSet: '',
        videos: [{
          videoUrl: '',
          imageUrl: '',
          existing: false
        }],
        imagePath: ''
      })
    },

    removeCampaignItem(index) {
      this.campaignItems.splice(index, 1)
    },

    addTitle(index) {
      this.campaignItems[index].titles.push({
        title: '',
        existing: false
      })
    },

    removeTitle(index, indexTitle) {
      this.campaignItems[index].titles.splice(indexTitle, 1)
    },

    addVideo(index) {
      this.campaignItems[index].videos.push({
        videoUrl: '',
        imageUrl: '',
        existing: false
      })
    },

    removeVideo(index, videoIndex) {
      this.campaignItems[index].videos.splice(videoIndex, 1)
    },

    removeImageSet(index) {
      this.campaignItems[index].imageSet = ''
      this.campaignItems[index].images = [{
        image: '',
        existing: false
      }]
    },

    removeVideoSet(index) {
      this.campaignItems[index].videoSet = ''
      this.campaignItems[index].videos = [{
        videoUrl: '',
        imageUrl: '',
        existing: false
      }]
    },

    removeTitleSet(index) {
      this.campaignItems[index].titleSet = ''
      this.campaignItems[index].titles = [{
        title: '',
        existing: false
      }]
    },

    removeDescriptionSet(index) {
      this.campaignItems[index].descriptionSet = ''
    },

    submitStep1() {
      const step1Data = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        advertiser: this.selectedAdvertiser,
        campaignName: this.campaignName,
        campaignBrandText: this.campaignBrandText,
        campaignIsActive: this.campaignIsActive,
        campaignCPC: this.campaignCPC,
        campaignSpendingLimit: this.campaignSpendingLimit,
        campaignSpendingLimitModel: this.campaignSpendingLimitModel,
        campaignMarketingObjective: this.campaignMarketingObjective,
        campaignCountryTargeting: this.campaignCountryTargeting,
        campaignPlatformTargeting: this.campaignPlatformTargeting,
        campaignStartDate: this.campaignStartDate,
        campaignEndDate: this.campaignEndDate
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      this.isLoading = true
      const step2Data = {
        campaignItems: this.campaignItems
      }
      this.postData = {...this.postData, ...step2Data }

      let url = '/campaigns'

      if (this.action == 'edit') {
        url += '/update/' + this.instance.instance_id
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
          this.isLoading = false
        } else {
          let me = this
          let interval = setInterval(function() {
            axios.post('/campaigns/item-status', {
              provider: me.selectedProvider,
              account: me.selectedAccount,
              advertiser: me.selectedAdvertiser,
              campaignId: response.data.id
            }).then(response => {
              if (response.data.errors) {
                alert(response.data.errors[0])
                me.isLoading = false
              } else if (response.data.status) {
                clearInterval(interval)
                window.location = '/campaigns/edit/' + response.data.campaign_id;
              }
            })
          }, 10000)
        }
      }).catch(error => {})
    }
  }
}
</script>
