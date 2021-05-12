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
                    <select2 id="device" name="device" v-model="campaignDevices" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device App</label>
                  <div class="col-sm-8">
                    <select2 id="device_app" name="device_app" v-model="campaignDeviceApps" :options="deviceApps" :settings="{ multiple: true, placeholder: 'ALL' }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device Os</label>
                  <div class="col-sm-8">
                    <select2 id="device_os" name="device_os" v-model="campaignDeviceOs" :options="deviceOs" :settings="{ multiple: true, placeholder: 'ALL' }" />
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
                          <div class="row mb-2" v-for="(headline, indexTitle) in content.headlines" :key="indexTitle">
                            <div class="col-sm-8">
                              <input type="text" name="headline" placeholder="Enter a headline" class="form-control" v-model="headline.headline" :disabled="content.titleSet.id" />
                            </div>
                            <div class="col-sm-4">
                              <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle)" v-if="indexTitle > 0" :disabled="content.titleSet.id"><i class="fa fa-minus"></i></button>
                              <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.headlines.length" :disabled="content.id || content.titleSet.id"><i class="fa fa-plus"></i></button>
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
                        <label for="ad_type" class="col-sm-4 control-label mt-2">Ad Type</label>
                        <div class="col-sm-8">
                          <div class="btn-group btn-group-toggle">
                            <label class="btn bg-olive" :class="{ active: content.adType === 'RESPONSIVE_IMAGE_AD' }">
                              <input type="radio" name="ad_type" autocomplete="off" value="RESPONSIVE_IMAGE_AD" v-model="content.adType"> IMAGE
                            </label>
                            <label class="btn bg-olive" :class="{ active: content.adType === 'RESPONSIVE_VIDEO_AD' }">
                              <input type="radio" name="ad_type" autocomplete="off" value="RESPONSIVE_VIDEO_AD" v-model="content.adType"> VIDEO
                            </label>
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
                          <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description" :disabled="content.descriptionSet.id"></textarea>
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
                      <div class="form-group row" v-if="content.adType === 'RESPONSIVE_IMAGE_AD'">
                        <label for="image" class="col-sm-4 control-label mt-2">Images</label>
                        <div class="col-sm-8">
                          <input type="text" name="image" placeholder="Images" class="form-control mb-2" disabled="disabled" v-model="content.imagePath" />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imagePath', index)" :disabled="content.imageSet.id">Choose Files</button>
                          <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                        </div>
                        <div class="col-sm-8 offset-sm-4" v-if="!content.imageSet.id">
                          <small class="text-danger" v-for="(image, indexImage) in content.images" :key="indexImage">
                            <span class="d-inline-block" v-if="image.image && !image.state">Image {{ image.image }} is invalid. You might need an 1200 x 628 image.</span>
                          </small>
                        </div>
                        <div class="col-sm-8 offset-sm-4 mt-2">
                          <span v-if="content.imageSet.id" class="selected-set">{{ content.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                      <div v-if="content.adType === 'RESPONSIVE_VIDEO_AD'">
                        <fieldset class="mb-3 p-3 rounded border" v-for="(video, videoIndex) in content.videos" :key="videoIndex">
                          <div class="form-group row">
                            <label for="video" class="col-sm-4 control-label mt-2">Video</label>
                            <div class="col-sm-8">
                              <input type="text" name="video" placeholder="Video" class="form-control" disabled="disabled" v-model="video.videoPath" />
                              <button type="button" class="btn btn-sm btn-default border mt-2" @click="openChooseFile('videoPath', index, videoIndex)" :disabled="content.videoSet.id">Choose File</button>
                            </div>
                          </div>

                          <div class="form-group row">
                            <label for="video_thumbnail_url" class="col-sm-4 control-label mt-2">Thumbnail</label>
                            <div class="col-sm-8">
                              <input type="text" name="video_thumbnail_url" placeholder="Enter thumbnail URL" class="form-control" disabled="disabled" v-model="video.videoThumbnailPath" />
                              <button type="button" class="btn btn-sm btn-default border mt-2" @click="openChooseFile('videoThumbnailPath', index, videoIndex)" :disabled="content.videoSet.id">Choose File</button>
                            </div>
                            <div class="col-sm-8 offset-sm-4">
                              <small class="text-danger" v-if="video.videoThumbnailPath && !video.videoThumbnailState">
                                <span class="d-inline-block">Image {{ video.videoThumbnailPath }} is invalid. You might need an 640 x 360 image.</span>
                              </small>
                            </div>
                          </div>
                          <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeVideo(index, videoIndex)" v-if="videoIndex > 0">Remove Video</button>
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
      return !this.selectedAdvertiser || !this.campaignName || !this.campaignGoals.length || !this.campaignBudget || this.campaignBudget <= 0 || !this.campaignStartDate || !this.adGroupName
    },
    submitStep2State() {
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].principal || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        if (!this.contents[i].titleSet.id) {
          for (let j = 0; j < this.contents[i].headlines.length; j++) {
            if (!this.contents[i].headlines[j].headline) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'RESPONSIVE_VIDEO_AD' && !this.contents[i].videoSet.id) {
          if (this.contents[i].videos.length == 0) {
            return false
          }

          for (let j = 0; j < this.contents[i].videos.length; j++) {
            if (this.contents[i].videos[j].mediaId) {
              continue
            }
            if (!this.contents[i].videos[j].videoPath || !this.contents[i].videos[j].videoThumbnailPath|| !this.contents[i].videos[j].videoThumbnailState) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'RESPONSIVE_IMAGE_AD' && !this.contents[i].imageSet.id) {
          if (this.contents[i].images.length == 0) {
            return false
          }

          for (let j = 0; j < this.contents[i].images.length; j++) {
            if (this.contents[i].images[j].mediaId) {
              continue
            }
            if (!this.contents[i].images[j].image || !this.contents[i].images[j].state) {
              return false
            }
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
      if (this.openingFileSelector === 'imagePath') {
        let paths = []
        this.contents[this.fileSelectorIndex].images = []
        for (let i = 0; i < values.length; i++) {
          this.contents[this.fileSelectorIndex].images.push({
            image: values[i].path,
            state: this.validDimensions(values[i].width, values[i].height, 1200, 628),
            existing: false
          })
          paths.push(values[i].path)
        }
        this.contents[this.fileSelectorIndex].imagePath = paths.join(';')
      } else if (this.openingFileSelector === 'videoPath') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoPath = values[0].path
      } else if (this.openingFileSelector === 'videoThumbnailPath') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoThumbnailPath = values[0].path
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoThumbnailState = this.validDimensions(values[0].width, values[0].height, 640, 360)
      }
      vm.$modal.hide('imageModal')
    });
    this.currentStep = this.step

    this.getAdvertisers()

    if (this.instance) {
      this.getCampaignGoals()
    }
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

      contents = [{
        id: '',
        adType: 'RESPONSIVE_IMAGE_AD',
        titleSet: '',
        headlines: [{
          headline: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        description: '',
        principal: '',
        imageSet: '',
        images: [],
        videoSet: '',
        videos: [{
          videoPath: '',
          videoThumbnailPath: '',
          existing: false
        }],
        imagePath: '',
        adPreviews: []
      }];
    if (this.instance) {
      if (this.instance.attributes) {
        for (let i = 0; i < this.instance.attributes.length; i++) {
          if (this.instance.attributes[i]['adGroupTargetList']['target'].targetType == 'AGE_TARGET') {
            campaignAges.push(this.instance.attributes[i]['adGroupTargetList']['target']['ageTarget']['age'])
          }

          if (this.instance.attributes[i]['adGroupTargetList']['target'].targetType == 'GENDER_TARGET') {
            campaignGenders.push(this.instance.attributes[i]['adGroupTargetList']['target']['genderTarget']['gender'])
          }

          if (this.instance.attributes[i]['adGroupTargetList']['target'].targetType == 'DEVICE_TARGET') {
            campaignDevices.push(this.instance.attributes[i]['adGroupTargetList']['target']['deviceTarget']['deviceType'])
          }
        }
      }

      campaignDeviceApps = this.instance.adGroups[0]['adGroup'].deviceApp
      campaignDeviceOs = this.instance.adGroups[0]['adGroup'].deviceOs

      contents = []

      for (let i = 0; i < this.instance.ads.length; i++) {
        let adType = this.instance.ads[i]['adGroupAd']['ad']['adType']
        let adKey = adType == 'RESPONSIVE_IMAGE_AD' ? 'responsiveImageAd' : 'responsiveVideoAd'
        contents.push({
          id: this.instance.ads[i]['adGroupAd']['adId'],
          adType: adType,
          titleSet: this.instance.ads[i]['titleSet'] || '',
          headlines: [{
            headline: this.instance.ads[i]['adGroupAd']['ad'][adKey]['headline'],
            existing: true
          }],
          displayUrl: this.instance.ads[i]['adGroupAd']['ad'][adKey]['displayUrl'],
          targetUrl: this.instance.ads[i]['adGroupAd']['ad'][adKey]['url'],
          descriptionSet:  this.instance.ads[i]['descriptionSet'] || '',
          description: this.instance.ads[i]['adGroupAd']['ad'][adKey]['description'],
          principal: this.instance.ads[i]['adGroupAd']['ad'][adKey]['principal'],
          imageSet:  this.instance.ads[i]['imageSet'] || '',
          images: [{
            mediaId: this.instance.ads[i]['adGroupAd']['mediaId'],
            existing: true
          }],
          videoSet:  this.instance.ads[i]['videoSet'] || '',
          videos: [{
            mediaId: this.instance.ads[i]['adGroupAd']['mediaId'],
            videoPath: this.instance.ads[i]['adGroupAd']['mediaId'],
            videoThumbnailId: adType == 'RESPONSIVE_VIDEO_AD' ? this.instance.ads[i]['adGroupAd']['ad']['responsiveVideoAd']['thumbnailMediaId'] : '',
            videoThumbnailPath: adType == 'RESPONSIVE_VIDEO_AD' ? this.instance.ads[i]['adGroupAd']['ad']['responsiveVideoAd']['thumbnailMediaId'] : '',
            videoThumbnailState: true,
            existing: true
          }],
          imagePath: this.instance.ads[i]['adGroupAd']['mediaId'],
          adPreviews: [],
        });
      }
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
      selectedAdvertiser: this.instance ? this.instance.accountId : '',
      campaignName: this.instance ? this.instance.campaignName : '',
      campaignBudget: this.instance ? this.instance.budget.amount : '',
      campaignStartDate: this.instance ? this.instance.startDate : this.$moment().add(1, 'days').format('YYYY-MM-DD'),
      campaignEndDate: this.instance ? this.instance.end_date : '',
      campaignBudgetDeliveryMethod: this.instance ? this.instance.budget.budgetDeliveryMethod : '',
      campaignBidStrategy: this.instance && this.instance.biddingStrategy ? this.instance.biddingStrategy.biddingStrategyType : '',
      campaignCampaignBidStrategy: this.instance ? this.instance.campaignBiddingStrategy.campaignBiddingStrategyType : 'AUTO',
      campaignMaxCpcBidValue: this.instance ? this.instance.campaignBiddingStrategy.maxCpcBidValue : '',
      campaignMaxCpvBidValue: this.instance ? this.instance.campaignBiddingStrategy.maxCpvBidValue : '',
      campaignMaxVcpmBidValue: this.instance ? this.instance.campaignBiddingStrategy.maxVcpmBidValue : '',
      campaignTargetCpaBidValue: this.instance ? this.instance.campaignBiddingStrategy.targetCpaBidValue : '',
      campaignStatus: this.instance ? this.instance.userStatus : 'ACTIVE',
      campaignGenders: campaignGenders,
      campaignAges: campaignAges,
      campaignDevices: campaignDevices,
      campaignDeviceApps: campaignDeviceApps,
      campaignDeviceOs: campaignDeviceOs,
      adGroupID: this.instance && this.instance.adGroups.length ? this.instance.adGroups[0]['adGroup']['adGroupId'] : '',
      adGroupName: this.instance && this.instance.adGroups.length ? this.instance.adGroups[0]['adGroup']['adGroupName'] : '',
      contents: contents,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      fileSelectorVideoIndex: 0,
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

      if (name == 'imagePath') {
        this.$root.$store.commit('fm/setSelectionType', 'multiple')
      } else {
        this.$root.$store.commit('fm/setSelectionType', 'single')
      }

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
      }
      if (this.setType == 'image') {
        this.contents[this.adSelectorIndex].imageSet = set
      }
      if (this.setType == 'video') {
        this.contents[this.adSelectorIndex].videoSet = set
      }
      if (this.setType == 'description') {
        this.contents[this.adSelectorIndex].descriptionSet = set
      }

      $('#creative-set-modal').modal('hide')
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validDimensions(fileWidth, fileHeight, width, height) {
      return fileWidth == width && fileHeight == height
    },
    addContent() {
      this.contents.push({
        id: '',
        adType: 'RESPONSIVE_IMAGE_AD',
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
        videos: [{
          videoPath: '',
          videoThumbnailPath: '',
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
    removeTitle(index, indexTitle) {
      this.contents[index].headlines.splice(indexTitle, 1)
    },

    addVideo(index) {
      this.contents[index].videos.push({
        videoPath: '',
        videoThumbnailPath: '',
        existing: false
      })
    },
    removeVideo(index, videoIndex) {
      this.contents[index].videos.splice(videoIndex, 1)
    },

    removeImageSet(index) {
      this.contents[index].imageSet = ''
      this.contents[index].images = [{
        imageUrlHQ: '',
        imageUrlHQState: true,
        imageUrl: '',
        imageUrlState: true,
        existing: false
      }]
    },
    removeVideoSet(index) {
      this.contents[index].videoSet = ''
      this.contents[index].videos = [{
        videoPrimaryUrl: '',
        videoPortraitUrl: '',
        imagePortraitUrl: '',
        existing: false
      }]
    },
    removeTitleSet(index) {
      this.contents[index].titleSet = ''
      this.contents[index].titles = [{
        title: '',
        existing: false
      }]
    },
    removeDescriptionSet(index) {
      this.contents[index].descriptionSet = ''
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
        campaignCampaignBidStrategy: this.campaignCampaignBidStrategy,
        campaignMaxCpcBidValue: this.campaignMaxCpcBidValue,
        campaignMaxCpvBidValue: this.campaignMaxCpvBidValue,
        campaignMaxVcpmBidValue: this.campaignMaxVcpmBidValue,
        campaignTargetCpaBidValue: this.campaignTargetCpaBidValue,
        adGroupID: this.adGroupID,
        adGroupName: this.adGroupName,
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
