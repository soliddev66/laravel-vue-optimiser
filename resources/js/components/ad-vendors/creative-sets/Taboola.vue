<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row">
      <div class="col">
        <h1 class="mb-2">Taboola</h1>

        <div class="form-group row">
          <label for="branding_text" class="col-sm-2 control-label mt-2">Branding Name</label>
          <div class="col-sm-8">
            <input type="text" name="branding_text" placeholder="Enter branding text" class="form-control" v-model="vendor.campaignBrandText" />
          </div>
        </div>
        <div class="form-group row">
          <label for="is_active" class="col-sm-2 control-label mt-2">Active</label>
          <div class="col-lg-10 col-xl-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignIsActive }">
                <input type="radio" name="is_active" id="is_active_1" autocomplete="off" value="true" v-model="vendor.campaignIsActive">TRUE
              </label>
              <label class="btn bg-olive" :class="{ active: !vendor.campaignIsActive }">
                <input type="radio" name="is_active" id="is_active_2" autocomplete="off" value="false" v-model="vendor.campaignIsActive">FALSE
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
              <input type="number" name="cpc" class="form-control" v-model="vendor.campaignCPC" id="cpc" />
              <div class="input-group-append">
                <span class="input-group-text">per click</span>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="spending_limit" class="col-sm-2 control-label mt-2">Budget</label>
          <div class="col-sm-4">
            <input type="number" name="spending_limit" class="form-control" v-model="vendor.campaignSpendingLimit" id="spending_limit" />
          </div>
          <div class="col-sm-4">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignSpendingLimitModel === 'MONTHLY' }">
                <input type="radio" name="type" id="spending_limit_model_2" autocomplete="off" value="MONTHLY" v-model="vendor.campaignSpendingLimitModel"> Per Month
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignSpendingLimitModel === 'ENTIRE' }">
                <input type="radio" name="type" id="spending_limit_model_3" autocomplete="off" value="ENTIRE" v-model="vendor.campaignSpendingLimitModel"> In Total
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="marketing_objective" class="col-sm-2 control-label mt-2">Marketing Objective</label>
          <div class="col-sm-8">
            <select2 id="marketing_objective" v-model="vendor.campaignMarketingObjective" :options="campaignMarketingObjectives" />
          </div>
        </div>
        <div class="form-group row">
          <label for="country_targeting" class="col-sm-2 control-label mt-2">Location</label>
          <div class="col-sm-8">
            <select2 id="country_targeting" name="country_targeting" v-model="vendor.campaignCountryTargeting" :options="countries" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="platform_targeting" class="col-sm-2 control-label mt-2">Device</label>
          <div class="col-sm-8">
            <select2 name="platform_targeting" v-model="vendor.campaignPlatformTargeting" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
          <div class="col-sm-3">
            <VueCtkDateTimePicker id="start_date" v-model="vendor.campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
          </div>
          <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
          <div class="col-sm-3">
            <VueCtkDateTimePicker id="end_date" v-model="vendor.campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
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
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker'

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css'
import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    vendor: {
      type: Object,
      default: null
    }
  },
  components: {
    Loading,
    Select2,
    VueCtkDateTimePicker
  },
  computed: {
    vendorState() {
      return this.vendor.campaignBrandText && this.vendor.campaignCPC && this.vendor.campaignSpendingLimit
    }
  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {
    //
  },
  data() {
    return {
      isLoading: false,
      fullPage: true,
      countries: [],
      campaignMarketingObjectives: [
        'BRAND_AWARENESS',
        'LEADS_GENERATION',
        'ONLINE_PURCHASES',
        'DRIVE_WEBSITE_TRAFFIC'
      ],
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
    }
  },
  methods: {
    preparingData() {
      this.getCountries()
    },

    getCountries() {
      this.isLoading = true
      this.countries = []
      axios.get(`/general/countries?provider=taboola&account=${this.vendor.selectedAccount}`).then(response => {
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
  }
}
</script>
