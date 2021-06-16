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
                <div class="form-group row">
                  <label for="advertiser" class="col-sm-2 control-label mt-2">Advertiser</label>
                  <div class="col-sm-4" v-if="advertisers.length">
                    <select name="advertiser" class="form-control" v-model="selectedAdvertiser" :disabled="instance">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in advertisers" :key="advertiser.id">{{ advertiser.id }} - {{ advertiser.name }}</option>
                    </select>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser">
                    <input type="text" name="advertiser_name" v-model="advertiserName" class="form-control" placeholder="Enter advertiser name...">
                  </div>
                  <div class="col-sm-2" v-if="saveAdvertiser && !instance">
                    <button type="button" class="btn btn-primary d-none" @click.prevent="saveAdvertiser = !saveAdvertiser">Create New</button>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser && advertiserName">
                    <button type="button" class="btn btn-success" @click.prevent="signUp()">Save</button>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser">
                    <button type="button" class="btn btn-warning" @click.prevent="saveAdvertiser = !saveAdvertiser">Cancel</button>
                  </div>
                </div>
                <h2 class="mt-3">Basic info</h2>
                <div class="form-group row">
                  <div class="col-sm-6">
                    <label for="name" class="control-label">Name</label>
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" id="name" />
                  </div>
                  <div class="col-sm-6">
                    <label class="control-label d-block">Campaign Objective</label>
                    <div class="btn-group btn-group-toggle">
                      <label :class="`btn bg-olive ${ campaignObjective === 'Awareness' ? 'active' : ''}`">
                        Awareness
                        <input type="radio" value="Awareness" autocomplete="off" v-model="campaignObjective" class="d-none">
                      </label>
                      <label :class="`btn bg-olive ${ campaignObjective === 'Traffic' ? 'active' : ''}`">
                        Traffic
                        <input type="radio" value="Traffic" autocomplete="off" v-model="campaignObjective" class="d-none">
                      </label>
                      <label :class="`btn bg-olive ${ campaignObjective === 'Conversions' ? 'active' : ''}`">
                        Conversions
                        <input type="radio" value="Conversions" autocomplete="off" v-model="campaignObjective" class="d-none">
                      </label>
                      <label :class="`btn bg-olive ${ campaignObjective === 'AppInstalls' ? 'active' : ''}`">
                        App Installs
                        <input type="radio" value="AppInstalls" autocomplete="off" v-model="campaignObjective" class="d-none">
                      </label>
                    </div>
                  </div>
                </div>
                <h2 class="mt-3">Scheduling</h2>
                <div class="form-group row">
                  <label for="schedule" class="col-sm-2 control-label mt-2">Schedule</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: scheduleType === 'CONTINUOUSLY' }">
                        <input type="radio" name="schedule" id="scheduleType1" autocomplete="off" value="CONTINUOUSLY" v-model="scheduleType"> Run continuously
                      </label>
                      <label class="btn bg-olive" :class="{ active: scheduleType === 'CUSTOM' }">
                        <input type="radio" name="schedule" id="scheduleType2" autocomplete="off" value="CUSTOM" v-model="scheduleType"> Set dates
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
                  <div class="col-sm-4">
                    <VueCtkDateTimePicker id="start_date" v-model="campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                    <VueCtkDateTimePicker id="start_time" v-model="campaignStartTime" format="hh:mm a" formatted="hh:mm a" :onlyTime="true" locale="en" label="Select Time"></VueCtkDateTimePicker>
                  </div>
                  <div class="col-sm-6">
                    Eastern Standard Time (UTC-05:00), NYC
                  </div>
                </div>
                <div class="form-group row" v-if="scheduleType === 'CUSTOM'">
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-4">
                    <VueCtkDateTimePicker id="end_date" v-model="campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                  <div class="col-sm-6"></div>
                </div>
                <h2 class="mt-3">Budgeting</h2>
                <div class="form-group row align-items-end">
                  <div class="col-sm-4">
                    <label for="costPerClick" class="control-label">Cost per Click</label>
                    <div class="input-group">
                      <div class="input-group-prepend">
                        <span class="input-group-text">$</span>
                      </div>
                      <input type="number" name="costPerClick" class="form-control" v-model="campaignCostPerClick" id="costPerClick" />
                      <div class="input-group-append">
                        <span class="input-group-text">per click</span>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-4">
                    <label for="budget" class="control-label">Budget</label>
                    <input type="number" name="budget" min="40" class="form-control" v-model="campaignBudget" id="budget" />
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
                  <label for="pacing" class="col-sm-2 control-label mt-2">Pacing</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignPacing === 'SPEND_ASAP' }">
                        <input type="radio" name="pacing" id="campaignPacing1" autocomplete="off" value="SPEND_ASAP" v-model="campaignPacing"> SPEND_ASAP
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignPacing === 'AUTOMATIC' }">
                        <input type="radio" name="pacing" id="campaignPacing2" autocomplete="off" value="AUTOMATIC" v-model="campaignPacing"> AUTOMATIC
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignPacing === 'DAILY_TARGET' }">
                        <input type="radio" name="pacing" id="campaignPacing3" autocomplete="off" value="DAILY_TARGET" v-model="campaignPacing"> DAILY_TARGET
                      </label>
                    </div>
                  </div>
                </div>
                <h2 class="mt-3">Targeting</h2>
                <div class="form-group row">
                  <label for="location" class="col-sm-12 control-label mt-2">Location</label>
                  <div class="col-sm-12">
                    <select2 id="location" v-model="campaignLocation" :options="countries" :settings="{ multiple: true }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="platform" class="col-sm-12 control-label mt-2">Platform Targeting</label>
                  <div class="col-sm-12">
                    <select2 id="platform" v-model="campaginPlatform" :options="platforms" :settings="{ multiple: true }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="operatingSystem" class="col-sm-12 control-label mt-2">Operating System</label>
                  <div class="col-sm-12">
                    <select2 id="operatingSystem" v-model="campaignOperatingSystem" :options="operatingSystems" :settings="{ multiple: true }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="browser" class="col-sm-12 control-label mt-2">Browsers</label>
                  <div class="col-sm-12">
                    <select2 id="browser" v-model="campaignBrowser" :options="browsers" :settings="{ multiple: true }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label class="col-sm-12 control-label mt-2">AdBlock</label>
                  <div class="col-sm-12">
                    <div class="form-check form-check-inline custom-checkbox">
                      <input type="checkbox" class="form-check-input custom-control-input" id="AdBlock" v-model="campaignExcludeAdBlockUsers">
                      <label for="AdBlock" class="form-check-label custom-control-label">
                        Exclude AdBlock users
                      </label>
                    </div>
                  </div>
                </div>
                <h2 class="mt-3">Tracking</h2>
                <div class="form-group row">
                  <label for="TrackingCode" class="col-sm-12 control-label mt-2">
                    Tracking Code
                  </label>
                  <div class="col-sm-6">
                    <textarea name="trackingCode" id="TrackingCode" cols="30" rows="5" class="form-control" v-model="campaignTrackingCode"></textarea>
                  </div>
                </div>
              </div>
              <div class="card-body" v-if="currentStep == 2">
                <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in ads" :key="index">
                  <div class="row">
                    <div class="col-sm-7">
                      <div class="form-group row">
                        <label for="title" class="col-sm-4 control-label mt-2">Title</label>
                        <div class="col-sm-8">
                          <div class="row mb-2" v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                            <div class="col-sm-8">
                              <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" :disabled="content.titleSet.id" />
                            </div>
                            <div class="col-sm-4">
                              <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle)" v-if="indexTitle > 0" :disabled="content.titleSet.id"><i class="fa fa-minus"></i></button>
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

                      <div class="form-group row">
                        <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name</label>
                        <div class="col-sm-8">
                          <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="content.brandname" />
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                        <div class="col-sm-8">
                          <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" :disabled="instance" />
                          <small class="text-danger" v-if="content.targetUrl && !validURL(content.targetUrl)">URL is invalid. You might need http/https at the beginning.</small>
                        </div>
                      </div>

                      <div class="form-group row">
                        <label for="image_url" class="col-sm-4 control-label mt-2">Media URL (Video / Image)</label>
                        <div class="col-sm-8">
                          <input type="text" name="image_url" placeholder="Select media" class="form-control" v-model="content.imageUrl" disabled="true" />
                          <div class="row mt-2 mb-2">
                            <div class="col">
                              <span v-if="content.imageSet.id" class="selected-set">{{ content.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                              <span v-if="content.videoSet.id" class="selected-set">{{ content.videoSet.name }}<span class="close" @click="removeVideoSet(index)"><i class="fas fa-times"></i></span></span>
                            </div>
                          </div>
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal', index)" :disabled="content.imageSet.id || content.videoSet.id">Choose File</button>
                          <button v-if="content.adType === 'IMAGE'" class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                          <button v-if="content.adType === 'VIDEO'" class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('video', index)">Load from Sets</button>
                        </div>
                        <div class="col-sm-8 offset-sm-4">
                          <small class="text-danger" v-for="(image, indexImage) in content.images" :key="indexImage">
                            <span class="d-inline-block" v-if="image.url && !validURL(image.url)">URL {{ image.url }} is invalid. You might need http/https at the beginning.</span>
                            <span class="d-inline-block" v-if="image.url && !image.state">Image {{ image.url }} is invalid. You might need an 1200 x 800 image.</span>
                          </small>
                        </div>
                      </div>
                    </div>
                    <div class="col-sm-5">
                      <h1>Preview</h1>
                      <section v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                        <section v-for="(image, indexImage) in content.images" :key="indexImage">
                          <div class="row no-gutters mb-2" v-if="image.url">
                            <div class="col-sm-5" v-if="content.adType == 'IMAGE'">
                              <img :src="image.url" class="card-img-top h-100">
                            </div>
                            <div class="col-sm-7">
                              <div class="card-body">
                                <h3 class="card-title">{{ title.title }}</h3>
                                <h6 class="card-text mt-5"><i>{{ content.brandname }}</i></h6>
                              </div>
                            </div>
                          </div>
                        </section>
                      </section>
                    </div>
                  </div>
                  <div class="row" v-if="index > 0">
                    <div class="col text-right">
                      <button class="btn btn-warning btn-sm" @click.prevent="removeAd(index)">Remove</button>
                    </div>
                  </div>
                </fieldset>
                <button class="btn btn-primary btn-sm d-none" @click.prevent="addAd()">Add New</button>
              </div>
            </form>
          </div>
          <div class="card-body" v-if="currentStep == 3">
            <div class="row mb-2" v-for="(attribute, index) in attributes" :key="attribute.id">
              <div class="col-sm-12" v-if="index === 0">
                <h4>Main Variation</h4>
              </div>
              <div class="col-sm-12 col-6">
                <div class="form-group row">
                  <label :for="`name${index}`" class="col-sm-4 control-label mt-2">Name</label>
                  <div class="col-sm-8">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="attribute.name" :disabled="index === 0" />
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-6">
                <div class="form-group row">
                  <label :for="`platform${index}`" class="col-sm-4 control-label mt-2">Platform</label>
                  <div class="col-sm-8">
                    <select2 :id="`platform${index}`" v-model="attribute.platform" :options="platforms" :settings="{ multiple: true }" />
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-6">
                <div class="form-group row">
                  <label :for="`cpc${index}`" class="col-sm-4 control-label mt-2">CPC</label>
                  <div class="col-sm-8">
                    <input type="number" name="cpc" placeholder="Enter cpc" class="form-control" v-model="attribute.cpc" :disabled="index === 0" />
                  </div>
                </div>
              </div>
              <div class="col-sm-12 col-6">
                <div class="form-group row">
                  <label :for="`targetUrl${index}`" class="col-sm-4 control-label mt-2">URL</label>
                  <div class="col-sm-8">
                    <input type="text" name="targetUrl" placeholder="Enter url" class="form-control" v-model="attribute.targetUrl" :disabled="index === 0" />
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
            <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in ads" :key="index">
              <div class="row">
                <div class="col-sm-6" v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                  <div class="row" v-for="(image, indexImage) in content.images" :key="indexImage">
                    <div v-if="image.url">
                      <div class="col-sm-5" v-if="content.adType == 'IMAGE'">
                        <img :src="image.url" class="card-img-top h-100">
                      </div>
                      <div class="col-sm-7">
                        <div class="card-body">
                          <h3 class="card-title">{{ title.title }}</h3>
                          <h6 class="card-text mt-5"><i>{{ content.brandname }}</i></h6>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </fieldset>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
            <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
          </div>
          <div class="d-flex justify-content-end" v-if="currentStep === 1">
            <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="!campaignNameState || !selectedAdvertiserState || !campaignBudgetState || !campaignCostPerClickState || !campaignStartDateState">Next</button>
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
    selectedAdvertiserState() {
      return this.selectedAdvertiser !== ''
    },
    campaignNameState() {
      return this.campaignName !== ''
    },
    campaignBudgetState() {
      return this.campaignBudget > 0
    },
    campaignCostPerClickState() {
      return this.campaignCostPerClick > 0
    },
    campaignStartDateState() {
      return this.campaignStartDate !== ''
    },
    campaignObjectiveState() {
      return this.campaignObjective !== ''
    },
    submitStep2State() {
      for (let i = 0; i < this.ads.length; i++) {
        if (!this.ads[i].brandname || !this.ads[i].targetUrl || !this.validURL(this.ads[i].targetUrl)) {
          return false
        }

        for (let j = 0; j < this.ads[i].titles.length; j++) {
          if (!this.ads[i].titles[j].title) {
            return false
          }
        }

        if (!this.ads[i].imageSet.id && !this.ads[i].videoSet.id && this.ads[i].images.length == 0) {
          return false
        }

        for (let j = 0; j < this.ads[i].images.length; j++) {
          if (!this.ads[i].images[j].url || !this.validURL(this.ads[i].images[j].url) || !this.ads[i].images[j].state) {
            return false
          }
        }
      }

      return true
    }
  },
  mounted() {
    let vm = this
    this.$root.$on('fm-selected-items', (values) => {
      if (this.openingFileSelector === 'imageModal') {
        this.ads[this.fileSelectorIndex].images = []
        let paths = []
        for (var i = 0; i < values.length; i++) {
          this.ads[this.fileSelectorIndex].images[i] = {
            url: process.env.MIX_APP_URL + '/storage/images/' + values[i].path,
            state: this.ads[this.fileSelectorIndex].adType == 'IMAGE' ? this.validDimensions(values[i].width, values[i].height, 1200, 800) : true,
            existing: false
          };
          paths.push(values[i].path)
        }
        this.ads[this.fileSelectorIndex].imageUrl = paths.join(';')
      }
      vm.$modal.hide(this.openingFileSelector)
    });
    this.currentStep = this.step

    this.getLanguages()
    this.getCountries()
    this.getAdvertisers()
  },
  watch: {
    //
  },
  data() {
    let dataAttributes = [],
      ads = [{
        id: '',
        adType: 'IMAGE',
        titleSet: '',
        titles: [{
          title: '',
          existing: false
        }],
        targetUrl: '',
        brandname: '',
        imageUrl: '',
        imageSet: '',
        videoSet: '',
        images: []
      }]

    if (this.instance) {
      ads = []
      let ad;
      for (let i = 0; i < this.instance.ads.length; i++) {
        ad = {
          id: this.instance.ads[i].id,
          adType: 'IMAGE',
          titleSet: this.instance.ads[i]['titleSet'] || '',
          titles: [{
            title: this.instance.ads[i].text,
            existing: true
          }],
          targetUrl: this.instance.ads[i].url,
          brandname: this.instance.ads[i].siteName,
          imageUrl: this.instance.ads[i].imageMetadata.originalImageUrl,
          imageSet: this.instance.ads[i]['imageSet'] || '',
          videoSet: this.instance.ads[i]['videoSet'] || '',
          images: [{
            url: this.instance.ads[i].imageMetadata.originalImageUrl,
            state: true,
            existing: true
          }]
        }

        if (this.instance.ads[i]['imageSet']) {
          ad.imageSet = this.instance.ads[i]['imageSet']
          ad.images = ad.imageSet.sets.map(item => {
            return {
              url: process.env.MIX_APP_URL + '/storage/images/' + item.hq_image,
              state: true
            }
          })
        }

        if (this.instance.ads[i]['videoSet']) {
          ad.videoSet = this.instance.ads[i]['videoSet']
        }

        ads.push(ad)
      }
    }

    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      currentStep: 1,
      saveAdvertiser: true,
      advertiserName: '',
      languages: [],
      countries: [],
      advertisers: [],
      accounts: [],
      actionName: this.action,
      fileSelectorIndex: 0,
      selectedAdvertiser: this.instance ? this.instance.marketerId : '',
      campaignName: this.instance ? this.instance.name : '',
      campaignObjective: this.instance ? this.instance.objective : 'Awareness', // Return
      campaignType: this.instance ? this.instance.channel : 'SEARCH_AND_NATIVE',
      campaignLanguage: this.instance ? this.instance.language : 'en',
      campaginPlatform: this.instance ? this.instance.targeting.platform : ['DESKTOP', 'MOBILE', 'TABLET'],
      campaignLocation: this.instance ? this.instance.targeting.locations : [],
      campaignOperatingSystem: this.instance ? this.instance.targeting.operatingSystems : ['Ios', 'MacOs', 'Android', 'Windows'],
      campaignBrowser: this.instance ? this.instance.targeting.browsers : ['Safari', 'Opera', 'Chrome', 'UCBrowser', 'InApp', 'Samsung', 'Firefox', 'InternetExplorer', 'Edge'],
      campaignBudget: this.instance ? this.instance.budget.amount : 20,
      campaignCostPerClick: this.instance ? this.instance.cpc : '',
      campaignPacing: this.instance ? this.instance.budget.pacing : 'SPEND_ASAP',
      campaignStartDate: this.instance ? this.instance.budget.startDate : this.$moment().format('YYYY-MM-DD'),
      campaignStartTime: this.instance ? this.instance.startHour : '',
      campaignEndDate: this.instance && !this.instance.budget.runForever ? this.instance.budget.endDate : '',
      platforms: ['DESKTOP', 'MOBILE', 'TABLET'],
      operatingSystems: ['Ios', 'MacOs', 'Android', 'Windows'],
      browsers: ['Safari', 'Opera', 'Chrome', 'UCBrowser', 'InApp', 'Samsung', 'Firefox', 'InternetExplorer', 'Edge'],
      campaignTrackingCode: this.instance ? this.instance.suffixTrackingCode : '',
      campaignExcludeAdBlockUsers: this.instance ? this.instance.targeting.excludeAdBlockUsers : true,
      campaignBudgetType: this.instance ? this.instance.budget.type : 'DAILY',
      scheduleType: this.instance && !this.instance.budget.runForever ? 'CUSTOM' : 'CONTINUOUSLY',
      ads: ads,
      attributes: [],
      dataAttributes: dataAttributes,
      openingFileSelector: '',
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
    openChooseFile(name, index) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show(name)
    },

    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },

    validDimensions(fileWidth, fileHeight, width, height) {
      return fileWidth == width && fileHeight == height
    },

    getLanguages() {
      this.isLoading = true
      this.languages = []
      axios.get(`/general/languages?provider=${this.selectedProvider}&account=${encodeURIComponent(this.selectedAccount)}`).then(response => {
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
      axios.get(`/general/countries?provider=${this.selectedProvider}&account=${encodeURIComponent(this.selectedAccount)}`).then(response => {
        if (response.data) {
          this.countries = response.data.map(country => {
            return {
              id: country.id,
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

    loadCreativeSet(type, index) {
      this.setType = type
      this.adSelectorIndex = index
      $('#creative-set-modal').modal('show')
    },

    selectCreativeSet(set) {
      if (this.setType == 'title') {
        this.ads[this.adSelectorIndex].titleSet = set
        this.loadTitleSets(this.adSelectorIndex).then(() => {
          this.ads[this.adSelectorIndex].titles = this.ads[this.adSelectorIndex].titleSet.sets.map(item => {
            return {
              title: item.title
            }
          })
        })
      }
      if (this.setType == 'image') {
        this.ads[this.adSelectorIndex].imageSet = set
        this.loadImageSets(this.adSelectorIndex).then(() => {
          this.ads[this.adSelectorIndex].images = this.ads[this.adSelectorIndex].imageSet.sets.map(item => {
            return {
              url: process.env.MIX_APP_URL + '/storage/images/' + item.hq_image,
              state: true
            }
          })
        })
      }
      if (this.setType == 'video') {
        this.ads[this.adSelectorIndex].videoSet = set
      }

      $('#creative-set-modal').modal('hide')
    },

    loadTitleSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/title-sets/${this.ads[index].titleSet.id}`).then(response => {
        this.ads[index].titleSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },

    loadImageSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/image-sets/${this.ads[index].imageSet.id}`).then(response => {
        this.ads[index].imageSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },

    removeImageSet(index) {
      this.ads[index].imageSet = ''
      this.ads[index].images = []
    },

    removeImageSet(index) {
      this.ads[index].videoSet = ''
      this.ads[index].images = []
    },

    removeTitleSet(index) {
      this.ads[index].titleSet = ''
      this.ads[index].titles = [{
        title: ''
      }]
    },

    addAd() {
      this.ads.push({
        id: '',
        adType: 'IMAGE',
        titleSet: '',
        titles: [{
          title: '',
          existing: false
        }],
        targetUrl: '',
        brandname: '',
        imageUrl: '',
        imageSet: '',
        videoSet: '',
        images: []
      })
    },

    removeAd(index) {
      this.ads.splice(index, 1)
    },

    addTitle(index) {
      this.ads[index].titles.push({
        title: '',
        existing: false
      })
    },

    removeTitle(index, indexTitle) {
      this.ads[index].titles.splice(indexTitle, 1)
    },

    removeAttibute(index) {
      this.attributes.splice(index, 1);
    },

    addNewAttibute() {
      this.attributes.push({
        name: '',
        platform: ['DESKTOP', 'MOBILE', 'TABLET'],
        cpc: '',
        targetUrl: this.targetUrl
      })
    },

    getAdvertisers() {
      this.advertisers = []
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${encodeURIComponent(this.selectedAccount)}`).then(response => {
        this.advertisers = response.data
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
        account: this.selectedAccount,
        name: this.advertiserName
      }).then(response => {
        alert('New advertiser has been saved!')
        this.advertiserName = ''
        this.saveAdvertiser = true
        this.getAdvertisers()
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
        budgetId: this.instance ? this.instance.budget.id : '',
        campaignType: this.campaignType,
        campaignLanguage: this.campaignLanguage,
        campaginPlatform: this.campaginPlatform,
        campaignLocation: this.campaignLocation,
        campaignOperatingSystem: this.campaignOperatingSystem,
        campaignBrowser: this.campaignBrowser,
        campaignAge: this.campaignAge,
        scheduleType: this.scheduleType,
        campaignStartDate: this.campaignStartDate,
        campaignPacing: this.campaignPacing,
        campaignCostPerClick: this.campaignCostPerClick,
        campaignObjective: this.campaignObjective,
        campaignStartTime: this.campaignStartTime,
        campaignExcludeAdBlockUsers: this.campaignExcludeAdBlockUsers,
        campaignTrackingCode: this.campaignTrackingCode,
        campaignEndDate: this.campaignEndDate
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },

    submitStep2() {
      const step2Data = {
        ads: this.ads,
      }
      this.postData = {...this.postData, ...step2Data }

      this.attributes[0] = {
        name: this.campaignName,
        platform: this.campaginPlatform,
        cpc: this.campaignCostPerClick,
        targetUrl: this.targetUrl
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
          this.$dialog.alert('Save successfully!').then(() => {
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

<style scoped>
</style>
