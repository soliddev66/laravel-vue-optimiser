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
                      <label class="btn bg-olive" :class="{ active: campaignSpendingLimitType === 'MONTHLY' }">
                        <input type="radio" name="type" id="spending_limit_model_2" autocomplete="off" value="MONTHLY" v-model="campaignSpendingLimitType"> Per Month
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignSpendingLimitType === 'ENTIRE' }">
                        <input type="radio" name="type" id="spending_limit_model_3" autocomplete="off" value="ENTIRE" v-model="campaignSpendingLimitType"> In Total
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
                    <select2 id="country_targeting" name="country_targeting" v-model="campaignCountryTargeting" :options="countries" :settings="{ multiple: true }" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="platform_targeting" class="col-sm-2 control-label mt-2">Device</label>
                  <div class="col-sm-8">
                    <select2 name="platform_targeting" v-model="campaignPlatformTargeting" :options="devices" :settings="{ multiple: true }" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
                  <div class="col-sm-3">
                    <input type="date" name="start_date" class="form-control" v-model="campaignStartDate" />
                  </div>
                  <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
                  <div class="col-sm-3">
                    <input type="date" name="end_date" class="form-control" v-model="campaignEndDate" />
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
        <div class="card-footer d-flex justify-content-end">
          <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
            <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
          </div>
          <div class="d-flex justify-content-end" v-if="currentStep === 1">
            <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="!submitStep1State">Next</button>
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
    submitStep1State() {

    },
    submitStep2State() {

    }
  },
  mounted() {
    this.currentStep = this.step

    this.getAdvertisers()
    this.getCountries()
  },
  watch: {

  },
  data() {

    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      currentStep: 1,
      actionName: this.action,
      advertisers: [],
      selectedAdvertiser: this.instance ? this.instance.advertiserId : '',
      campaignName: '',
      campaignBrandText: '',
      campaignCPC: '',
      campaignSpendingLimit: '',
      campaignSpendingLimitType: 'MONTHLY',
      campaignMarketingObjective: 'DRIVE_WEBSITE_TRAFFIC',
      campaignMarketingObjectives: [
        'BRAND_AWARENESS',
        'LEADS_GENERATION',
        'ONLINE_PURCHASES',
        'DRIVE_WEBSITE_TRAFFIC'
      ],
      campaignCountryTargeting: [],
      countries: [],
      campaignStartDate: '',
      campaignEndDate: '',
      campaignPlatformTargeting: [],
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
      campaignIsActive: false,
    }
  },
  methods: {
    getAdvertisers() {
      this.advertisers = []
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.advertisers = response.data
        console.log(this.advertisers)
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    getCountries() {
      this.isLoading = true
      this.countries = []
      axios.get(`/general/countries?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        console.log(response.data)
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
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    submitStep1() {
      const step1Data = {

      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {

      }
      this.postData = {...this.postData, ...step2Data }
      this.currentStep = 3
    },
    submitStep3() {
      const step3Data = {

      }
      this.postData = {...this.postData, ...step3Data }
      this.currentStep = 4
    },
    submitStep4() {

    }
  }
}
</script>