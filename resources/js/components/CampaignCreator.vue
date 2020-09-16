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
          <div class="card-body" v-if="currentStep == 1">
            <select v-model="selectedProvider">
              <option value="">Select Traffic Source</option>
              <option :value="provider.slug" v-for="provider in providers">{{ provider.label }}</option>
            </select>
            <select v-model="selectedAccount" @change="getAdvertisers()">
              <option value="">Select Account</option>
              <option :value="account.open_id" v-for="account in accounts">{{ account.open_id }}</option>
            </select>
            <hr>
            <form class="form-horizontal" v-if="selectedProvider && selectedAccount">
              <h2>General information</h2>
              <div class="form-group row">
                <label for="advertiser" class="col-sm-2 control-label mt-2">Advertiser</label>
                <div class="col-sm-6">
                  <select name="advertiser" class="form-control" v-model="selectedAdvertiser">
                    <option value="">Select Advertiser</option>
                    <option :value="advertiser.id" v-for="advertiser in advertisers">{{ advertiser.id }} - {{ advertiser.advertiserName }}</option>
                  </select>
                </div>
                <div class="col-sm-2" v-if="!saveAdvertiser">
                  <input type="text" name="advertiser_name" v-model="advertiserName" class="form-control" placeholder="Enter advertiser name...">
                </div>
                <button type="button" class="col-sm-2 btn btn-primary" v-if="saveAdvertiser" @click.prevent="saveAdvertiser = !saveAdvertiser">Create New</button>
                <button type="button" class="col-sm-2 btn btn-success" v-if="!saveAdvertiser && advertiserName" @click.prevent="signUp()">Save</button>
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
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn bg-olive" :class="{ active: campaignType === 'NATIVE' }">
                      <input type="radio" name="options" id="campaignType1" autocomplete="off" value="NATIVE" v-model="campaignType"> Native Only
                    </label>
                    <label class="btn bg-olive" :class="{ active: campaignType === 'SEARCH' }">
                      <input type="radio" name="options" id="campaignType2" autocomplete="off" value="SEARCH" v-model="campaignType"> Search Only
                    </label>
                    <label class="btn bg-olive" :class="{ active: campaignType === 'SEARCH_AND_NATIVE' }">
                      <input type="radio" name="options" id="campaignType3" autocomplete="off" value="SEARCH_AND_NATIVE" v-model="campaignType"> Search and Native
                    </label>
                  </div>
                </div>
              </div>
              <h2>Define your audience</h2>
              <div class="form-group row">
                <label for="language" class="col-sm-2 control-label mt-2">Language</label>
                <div class="col-sm-8">
                  <select name="language" class="form-control" v-model="campaignLanguage">
                    <option value="en">English</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="location" class="col-sm-2 control-label mt-2">Location</label>
                <div class="col-sm-8">
                  <select name="location" class="form-control" multiple v-model="campaignLocation">
                    <option value="US">US</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="gender" class="col-sm-2 control-label mt-2">Gender</label>
                <div class="col-sm-8">
                  <select name="gender" class="form-control" v-model="campaignGender">
                    <option value="all">All</option>
                    <option value="male">Male</option>
                    <option value="female">Female</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="age" class="col-sm-2 control-label mt-2">Age</label>
                <div class="col-sm-8">
                  <select name="age" class="form-control" v-model="campaignAge">
                    <option value="all">All</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="device" class="col-sm-2 control-label mt-2">Device</label>
                <div class="col-sm-8">
                  <select name="device" class="form-control" v-model="campaignDevice">
                    <option value="all">All</option>
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
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn bg-olive" :class="{ active: campaignBudgetType === 'DAILY' }">
                      <input type="radio" name="options" id="campaignBudgetType1" autocomplete="off" value="DAILY" v-model="campaignBudgetType"> Per Day
                    </label>
                    <label class="btn bg-olive" :class="{ active: campaignBudgetType === 'MONTHLY' }">
                      <input type="radio" name="options" id="campaignBudgetType2" autocomplete="off" value="MONTHLY" v-model="campaignBudgetType"> Per Month
                    </label>
                    <label class="btn bg-olive" :class="{ active: campaignBudgetType === 'LIFETIME' }">
                      <input type="radio" name="options" id="campaignBudgetType3" autocomplete="off" value="LIFETIME" v-model="campaignBudgetType"> In Total
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group row">
                <label for="bid_strategy" class="col-sm-2 control-label mt-2">Bid Strategy</label>
                <div class="col-sm-8">
                  <select name="bid_strategy" class="form-control" v-model="campaignStrategy">
                    <option value="CPC">Enhanced CPC</option>
                  </select>
                </div>
              </div>
              <div class="form-group row">
                <label for="conversion_counting" class="col-sm-2 control-label mt-2">Conversion counting</label>
                <div class="col-sm-8">
                  <select name="conversion_counting" class="form-control" v-model="campaignConversionCounting">
                    <option value="all">All per interaction</option>
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
                  <div class="btn-group btn-group-toggle" data-toggle="buttons">
                    <label class="btn bg-olive" :class="{ active: scheduleType === 'IMMEDIATELY' }">
                      <input type="radio" name="options" id="scheduleType1" autocomplete="off" value="IMMEDIATELY" v-model="scheduleType"> Start running ads immediately
                    </label>
                    <label class="btn bg-olive" :class="{ active: scheduleType === 'CUSTOM' }">
                      <input type="radio" name="options" id="scheduleType2" autocomplete="off" value="CUSTOM" v-model="scheduleType"> Set a start and end date
                    </label>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="card-body" v-if="currentStep == 2">
          </div>
          <div class="card-body" v-if="currentStep == 3">
          </div>
          <div class="card-body" v-if="currentStep == 4">
          </div>
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 4 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2">Next</button>
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
import axios from 'axios'

export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    trackers: {
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
  mounted() {
    console.log('Component mounted.')
    this.currentStep = this.step
    console.log(this.providers)
  },
  data() {
    return {
      currentStep: 1,
      saveAdvertiser: true,
      advertiserName: '',
      redtrackKey: '',
      advertisers: [],
      selectedProvider: 'yahoo',
      selectedAccount: '',
      selectedAdvertiser: '',
      campaignName: '',
      campaignType: 'NATIVE',
      campaignLanguage: 'en',
      campaignLocation: ['US'],
      campaignGender: 'all',
      campaignAge: 'all',
      campaignDevice: 'all',
      campaignBudget: '',
      campaignBudgetType: 'DAILY',
      campaignStrategy: 'CPC',
      campaignConversionCounting: 'all',
      adGroupName: '',
      bidAmount: '0.05',
      scheduleType: 'IMMEDIATELY',
      selectedTracker: 'redtrack'
    }
  },
  methods: {
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
    submitStep1(useTracker) {
      axios.post('/campaigns', {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.selectedAdvertiser,
        campaignBudget: this.campaignBudget,
        campaignBudgetType: this.campaignBudgetType,
        campaignName: this.campaignName,
        adGroupName: this.adGroupName,
        bidAmount: this.bidAmount,
        campaignType: this.campaignType,
        campaignStrategy: this.campaignStrategy
      }).then(response => {
        alert('New campaign has been saved!')
        this.currentStep = 2
      }).catch(err => {
        console.log(err)
      })
    },
    submitStep2(useTracker) {
      window.location = `/login/${this.selectedTracker}?api_key=${this.redtrackKey}`
    }
  }
}
</script>

<style>
</style>
