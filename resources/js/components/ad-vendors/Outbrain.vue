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
                    <input type="date" name="start_date" class="form-control" v-model="campaignStartDate" />
                    <input type="time" name="start_time" class="form-control" v-model="campaignStartTime" />
                  </div>
                  <div class="col-sm-6">
                    Eastern Standard Time (UTC-05:00), NYC
                  </div>
                </div>
                <div class="form-group row" v-if="scheduleType === 'CUSTOM'">
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-4">
                    <input type="date" name="end_date" class="form-control" v-model="campaignEndDate" />
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
                  <label for="schedule" class="col-sm-2 control-label mt-2">Pacing</label>
                  <div class="col-sm-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignPacing === 'SPEND_ASAP' }">
                        <input type="radio" name="schedule" id="campaignPacing1" autocomplete="off" value="SPEND_ASAP" v-model="campaignPacing"> SPEND_ASAP
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignPacing === 'AUTOMATIC' }">
                        <input type="radio" name="schedule" id="campaignPacing2" autocomplete="off" value="AUTOMATIC" v-model="campaignPacing"> AUTOMATIC
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignPacing === 'DAILY_TARGET' }">
                        <input type="radio" name="schedule" id="campaignPacing3" autocomplete="off" value="DAILY_TARGET" v-model="campaignPacing"> DAILY_TARGET
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
                  <label class="col-sm-12 control-label mt-2">Outbrain Extended Network</label>
                  <div class="col-sm-12">
                    <div class="form-check form-check-inline custom-checkbox">
                      <input type="checkbox" class="form-check-input custom-control-input" v-model="campaignUseNetworkExtendedTraffic" id="OutbrainExtendedNetwork">
                      <label for="OutbrainExtendedNetwork" class="form-check-label custom-control-label">
                        Use extended network traffic
                      </label>
                    </div>
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
                      <label for="cpc" class="col-sm-4 control-label mt-2">CPC</label>
                      <div class="col-sm-8">
                        <input type="number" name="cpc" placeholder="Enter cpc" class="form-control" v-model="cpc" />
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
                      <label for="image_url" class="col-sm-4 control-label mt-2">Image URL</label>
                      <div class="col-sm-8">
                        <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="imageUrl" />
                      </div>
                      <div class="col-sm-8 offset-sm-4">
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal')">Choose File</button>
                      </div>
                      <div class="col-sm-8 offset-sm-4 text-center">
                        <small class="text-danger" v-if="imageUrl && !imageUrlState">URL is invalid. You might need http/https at the beginning.</small>
                        <small class="text-danger" v-if="image.size && !imageState">Image is invalid. You might need an 627x627 image.</small>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-6">
                    <h1>Preview</h1>
                    <div class="card" v-if="imageUrlState">
                      <div class="row no-gutters">
                        <div class="col-sm-5">
                          <img :src="imageUrl" class="card-img-top h-100">
                        </div>
                        <div class="col-sm-7">
                          <div class="card-body">
                            <h3 class="card-title">{{ title }}</h3>
                            <h6 class="card-text mt-5"><i>{{ brandname }}</i></h6>
                          </div>
                        </div>
                      </div>
                    </div>
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
            <div class="col-sm-12">
              <div class="card" v-if="imageUrlState">
                <div class="row no-gutters">
                  <div class="col-sm-5">
                    <img :src="imageUrl" class="card-img-top h-100">
                  </div>
                  <div class="col-sm-7">
                    <div class="card-body">
                      <h3 class="card-title">{{ title }}</h3>
                      <h6 class="card-text mt-5"><i>{{ brandname }}</i></h6>
                    </div>
                  </div>
                </div>
              </div>
            </div>
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
            <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="!titleState || !brandnameState || !cpcState || !targetUrlState || !imageUrlState">Next</button>
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
    campaignCostPerClickState() {
      return this.campaignCostPerClick > 0
    },
    campaignStartDateState() {
      return this.campaignStartDate !== ''
    },
    campaignObjectiveState() {
      return this.campaignObjective !== ''
    },
    titleState() {
      return this.title !== ''
    },
    brandnameState() {
      return this.brandname !== ''
    },
    cpcState() {
      return this.cpc > 0
    },
    targetUrlState() {
      return this.targetUrl !== '' && this.validURL(this.targetUrl)
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
      if (this.openingFileSelector === 'imageModal') {
        this.imageUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
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
    let campaginPlatform = ['DESKTOP', 'MOBILE', 'TABLET']
    let campaignLocation = []
    let campaignOperatingSystem = ['Ios', 'Android', 'MacOs', 'Windows']
    let campaignBrowser = ['chrome', 'firefox', 'safari', 'internetExplorer', 'opera', 'samsung', 'ucBrowser', 'inApp']
    let adGroupID = ''
    let dataAttributes = []
    if (this.instance) {
      this.instance.attributes.forEach(attribute => {
        if (attribute.type === 'DEVICE') {
          campaginPlatform = attribute.value;
        } else if (attribute.type === 'WOEID') {
          campaignLocation.push(attribute.value);
        }
        dataAttributes.push(attribute.id);
      });
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
      selectedAdvertiser: this.instance ? this.instance.advertiserId : '',
      campaignName: this.instance ? this.instance.campaignName : '',
      campaignObjective: this.instance ? this.instance.campaignObjective : 'Awareness', // Return
      campaignType: this.instance ? this.instance.channel : 'SEARCH_AND_NATIVE',
      campaignLanguage: this.instance ? this.instance.language : 'en',
      campaginPlatform: campaginPlatform,
      campaignLocation: campaignLocation,
      campaignOperatingSystem: campaignOperatingSystem,
      campaignBrowser: campaignBrowser,
      campaignBudget: this.instance ? this.instance.budget : 20,
      campaignCostPerClick: this.instance ? this.instance.costPerClick : '',
      campaignPacing: this.instance ? this.instance.campaignPacing : 'SPEND_ASAP',
      campaignStartDate: '',
      campaignStartTime: '',
      campaignEndDate: '',
      platforms: ['DESKTOP', 'MOBILE', 'TABLET'],
      operatingSystems: ['Ios', 'Android', 'MacOs', 'Windows'],
      browsers: ['chrome', 'firefox', 'safari', 'internetExplorer', 'opera', 'samsung', 'ucBrowser', 'inApp'],
      campaignTrackingCode: this.instance ? this.instance.trackingCode : '', // Return
      campaignUseNetworkExtendedTraffic: this.instance ? this.instance.useNetworkExtendedTraffic : true, // Return
      campaignExcludeAdBlockUsers: this.instance ? this.instance.excludeAdBlockUsers : true, // Return
      campaignBudgetType: this.instance ? this.instance.budgetType : 'DAILY',
      scheduleType: 'CONTINUOUSLY',
      title: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['title'] : '',
      targetUrl: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['landingUrl'] : '',
      cpc: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['cpc'] : '',
      brandname: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['sponsoredBy'] : '',
      imageUrl: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['imageUrl'] : '',
      image: {
        size: '',
        height: '',
        width: ''
      },
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
      if (image.width === 1200 && image.height === 800) {
        return true
      }
      return false;
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
    selectedAccountChanged() {
      this.getLanguages()
      this.getCountries()
      this.getAdvertisers()
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
        console.log(response.data)
        this.advertisers = response.data.marketers
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
        adID: this.instance && this.instance.ads.length > 0 ? this.instance.ads[0]['id'] : '',
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
        campaignTrackingCode: this.campaignTrackingCode,
        campaignEndDate: this.campaignEndDate
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {
        targetUrl: this.targetUrl,
        title: this.title,
        cpc: this.cpc,
        brandname: this.brandname,
        imageUrl: this.imageUrl,
        dataAttributes: this.dataAttributes
      }
      this.postData = {...this.postData, ...step2Data }
      this.attributes[0] = {
        name: this.campaignName,
        platform: this.campaginPlatform,
        cpc: this.cpc,
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

<style scoped>
</style>
