<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <label class="p-2" :class="{ 'bg-primary': currentStep === 1 }">Create Campaign</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">Add Contents</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Generate Variations</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 4 }">Preview</label>
          </div>
          <div class="card-body">
            <select v-model="selectedProvider">
              <option value="">Select Traffic Source</option>
              <option :value="provider.slug" v-for="provider in providers">{{ provider.label }}</option>
            </select>
            <select v-model="selectedAccount" @change="selectedAccountChanged()">
              <option value="">Select Account</option>
              <option :value="account.open_id" v-for="account in accounts">{{ account.open_id }}</option>
            </select>
            <hr>
            <form class="form-horizontal" v-if="selectedProvider && selectedAccount && languages.length && countries.length">
              <div v-if="currentStep == 1">
                <h2>General information</h2>
                <div class="form-group row">
                  <label for="advertiser" class="col-sm-2 control-label mt-2">Advertiser</label>
                  <div class="col-sm-4" v-if="advertisers.length">
                    <select name="advertiser" class="form-control" v-model="selectedAdvertiser">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in advertisers">{{ advertiser.id }} - {{ advertiser.advertiserName }}</option>
                    </select>
                  </div>
                  <div class="col-sm-2" v-if="!saveAdvertiser">
                    <input type="text" name="advertiser_name" v-model="advertiserName" class="form-control" placeholder="Enter advertiser name...">
                  </div>
                  <div class="col-sm-2" v-if="saveAdvertiser">
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
                    <select2 name="language" :options="languages" v-model="campaignLanguage"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="location" class="col-sm-2 control-label mt-2">Location</label>
                  <div class="col-sm-8">
                    <Select2 name="location" v-model="campaignLocation" :options="countries" :settings="{ multiple: true }" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="gender" class="col-sm-2 control-label mt-2">Gender</label>
                  <div class="col-sm-8">
                    <select name="gender" class="form-control" v-model="campaignGender">
                      <option value="">All</option>
                      <option value="MALE">Male</option>
                      <option value="FEMALE">Female</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="age" class="col-sm-2 control-label mt-2">Age</label>
                  <div class="col-sm-8">
                    <select name="age" class="form-control" v-model="campaignAge">
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
                <div class="form-group row">
                  <label for="device" class="col-sm-2 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select name="device" class="form-control" v-model="campaignDevice">
                      <option value="">All</option>
                      <option value="SMARTPHONE">SMARTPHONE</option>
                      <option value="TABLET">TABLET</option>
                      <option value="DESKTOP">DESKTOP</option>
                    </select>
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
                    <input type="number" name="bid_cpc" min="0.05" class="form-control" v-model="bidAmount" />
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
                    <input type="date" name="start_date" class="form-control" v-model="campaignStartDate" />
                  </div>
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-4">
                    <input type="date" name="end_date" class="form-control" v-model="campaignEndDate" />
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
                      <div class="col-sm-8">
                        <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="displayUrl" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                      <div class="col-sm-8">
                        <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="targetUrl" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="image_hq_url" class="col-sm-4 control-label mt-2">Image HQ URL</label>
                      <div class="col-sm-8">
                        <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="imageUrlHQ" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="image_url" class="col-sm-4 control-label mt-2">Image URL</label>
                      <div class="col-sm-8">
                        <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="imageUrl" />
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
            <div class="row mb-2" v-for="(attribute, index) in attributes">
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
                    <select :name="`age${index}`" class="form-control" v-model="attribute.age">
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
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 4 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="!selectedProviderState || !selectedAccountState || !campaignNameState || !selectedAdvertiserState || !campaignBudgetState || !adGroupNameState || !bidAmountState">Next</button>
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
  </div>
</template>

<script>
import _ from 'lodash'
import Select2 from 'v-select2-component';
import axios from 'axios'

export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    accounts: {
      type: Array,
      default: []
    },
    step: {
      type: Number,
      default: 1
    }
  },
  components: {
    Select2
  },
  computed: {
    selectedProviderState() {
      return this.selectedProvider !== ''
    },
    selectedAccountState() {
      return this.selectedAccount !== ''
    },
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
      return this.imageUrlHQ !== '' && this.validURL(this.imageUrlHQ)
    },
    imageUrlState() {
      return this.imageUrl !== '' && this.validURL(this.imageUrl)
    }
  },
  mounted() {
    console.log('Component mounted.')
    this.currentStep = this.step
    console.log(this.providers)
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
    return {
      postData: {},
      currentStep: 1,
      saveAdvertiser: true,
      advertiserName: '',
      redtrackKey: '',
      languages: [],
      countries: [],
      advertisers: [],
      selectedProvider: 'yahoo',
      selectedAccount: '',
      selectedAdvertiser: '',
      campaignName: '',
      campaignType: 'SEARCH_AND_NATIVE',
      campaignLanguage: 'en',
      campaignLocation: [],
      campaignGender: '',
      campaignAge: '',
      campaignDevice: '',
      campaignBudget: '',
      campaignStartDate: '',
      campaignEndDate: '',
      campaignBudgetType: 'DAILY',
      campaignStrategy: 'OPT_ENHANCED_CPC',
      campaignConversionCounting: 'ALL_PER_INTERACTION',
      adGroupName: '',
      bidAmount: '0.05',
      scheduleType: 'IMMEDIATELY',
      title: '',
      displayUrl: '',
      targetUrl: '',
      description: '',
      brandname: '',
      imageUrlHQ: '',
      imageUrl: '',
      previewData: '',
      attributes: []
    }
  },
  methods: {
    validURL(str) {
      var pattern = new RegExp('^(https?:\\/\\/)' + // protocol
        '((([a-z\\d]([a-z\\d-]*[a-z\\d])*)\\.)+[a-z]{2,}|' + // domain name
        '((\\d{1,3}\\.){3}\\d{1,3}))' + // OR ip (v4) address
        '(\\:\\d+)?(\\/[-a-z\\d%_.~+]*)*' + // port and path
        '(\\?[;&a-z\\d%_.~+=-]*)?' + // query string
        '(\\#[-a-z\\d_]*)?$', 'i'); // fragment locator
      return !!pattern.test(str);
    },
    loadPreview() {
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
      })
    },
    selectedAccountChanged() {
      this.getLanguages()
      this.getCountries()
      this.getAdvertisers()
    },
    getLanguages() {
      axios.get(`/general/languages?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.languages = response.data.response.map(language => {
          return {
            id: language.value,
            text: language.name.toUpperCase()
          }
        })
      }).catch(err => {
        console.log(err)
      })
    },
    getCountries() {
      axios.get(`/general/countries?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.countries = response.data.response.map(country => {
          return {
            id: country.woeid,
            text: country.name
          }
        })
      }).catch(err => {
        console.log(err)
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
    getAdvertisers() {
      if (this.selectedAccount) {
        axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
          this.advertisers = response.data.response
        }).catch(err => {
          console.log(err)
        })
      } else {
        this.advertisers = []
      }
    },
    signUp() {
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
        displayUrl: this.displayUrl,
        targetUrl: this.targetUrl,
        title: this.title,
        brandname: this.brandname,
        description: this.description,
        imageUrlHQ: this.imageUrlHQ,
        imageUrl: this.imageUrl
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
      axios.post('/campaigns', this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          alert('Campaign created!');
        }
      }).catch(error => {
        console.log(error)
      })
    }
  }
}
</script>

<style>
.select2-container .select2-selection--single {
  min-height: 28px;
  height: auto;
}
</style>
