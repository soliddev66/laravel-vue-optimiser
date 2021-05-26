<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
     <div class="row">
      <div class="col">
        <h1>Outbrain</h1>
        <div class="form-group row">
          <div class="col-sm-6">
            <label class="control-label d-block">Campaign Objective</label>
            <div class="btn-group btn-group-toggle">
              <label :class="`btn bg-olive ${ vendor.campaignObjective === 'Awareness' ? 'active' : ''}`">
                Awareness
                <input type="radio" value="Awareness" autocomplete="off" v-model="vendor.campaignObjective" class="d-none">
              </label>
              <label :class="`btn bg-olive ${ vendor.campaignObjective === 'Traffic' ? 'active' : ''}`">
                Traffic
                <input type="radio" value="Traffic" autocomplete="off" v-model="vendor.campaignObjective" class="d-none">
              </label>
              <label :class="`btn bg-olive ${ vendor.campaignObjective === 'Conversions' ? 'active' : ''}`">
                Conversions
                <input type="radio" value="Conversions" autocomplete="off" v-model="vendor.campaignObjective" class="d-none">
              </label>
              <label :class="`btn bg-olive ${ vendor.campaignObjective === 'AppInstalls' ? 'active' : ''}`">
                App Installs
                <input type="radio" value="AppInstalls" autocomplete="off" v-model="vendor.campaignObjective" class="d-none">
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
            <VueCtkDateTimePicker id="start_date" v-model="vendor.campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
            <VueCtkDateTimePicker id="start_time" v-model="vendor.campaignStartTime" format="hh:mm a" formatted="hh:mm a" :onlyTime="true" locale="en" label="Select Time"></VueCtkDateTimePicker>
          </div>
          <div class="col-sm-6">
            Eastern Standard Time (UTC-05:00), NYC
          </div>
        </div>
        <div class="form-group row" v-if="scheduleType === 'CUSTOM'">
          <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
          <div class="col-sm-4">
            <VueCtkDateTimePicker id="end_date" v-model="vendor.campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
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
              <input type="number" name="costPerClick" class="form-control" v-model="vendor.campaignCostPerClick" id="costPerClick" />
              <div class="input-group-append">
                <span class="input-group-text">per click</span>
              </div>
            </div>
          </div>
          <div class="col-sm-4">
            <label for="budget" class="control-label">Budget</label>
            <input type="number" name="budget" min="40" class="form-control" v-model="vendor.campaignBudget" id="budget" />
          </div>
          <div class="col-sm-4">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignBudgetType === 'DAILY' }">
                <input type="radio" name="type" id="campaignBudgetType1" autocomplete="off" value="DAILY" v-model="vendor.campaignBudgetType"> Per Day
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignBudgetType === 'MONTHLY' }">
                <input type="radio" name="type" id="campaignBudgetType2" autocomplete="off" value="MONTHLY" v-model="vendor.campaignBudgetType"> Per Month
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignBudgetType === 'LIFETIME' }">
                <input type="radio" name="type" id="campaignBudgetType3" autocomplete="off" value="LIFETIME" v-model="vendor.campaignBudgetType"> In Total
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="pacing" class="col-sm-2 control-label mt-2">Pacing</label>
          <div class="col-sm-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignPacing === 'SPEND_ASAP' }">
                <input type="radio" name="pacing" id="campaignPacing1" autocomplete="off" value="SPEND_ASAP" v-model="vendor.campaignPacing"> SPEND_ASAP
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignPacing === 'AUTOMATIC' }">
                <input type="radio" name="pacing" id="campaignPacing2" autocomplete="off" value="AUTOMATIC" v-model="vendor.campaignPacing"> AUTOMATIC
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignPacing === 'DAILY_TARGET' }">
                <input type="radio" name="pacing" id="campaignPacing3" autocomplete="off" value="DAILY_TARGET" v-model="vendor.campaignPacing"> DAILY_TARGET
              </label>
            </div>
          </div>
        </div>
        <h2 class="mt-3">Targeting</h2>
        <div class="form-group row">
          <label for="location" class="col-sm-12 control-label mt-2">Location</label>
          <div class="col-sm-12">
            <select2 v-model="vendor.campaignLocation" :options="countries" :settings="{ multiple: true }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="platform" class="col-sm-12 control-label mt-2">Platform Targeting</label>
          <div class="col-sm-12">
            <select2 v-model="vendor.campaginPlatform" :options="platforms" :settings="{ multiple: true }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="operatingSystem" class="col-sm-12 control-label mt-2">Operating System</label>
          <div class="col-sm-12">
            <select2 v-model="vendor.campaignOperatingSystem" :options="operatingSystems" :settings="{ multiple: true }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="browser" class="col-sm-12 control-label mt-2">Browsers</label>
          <div class="col-sm-12">
            <select2 v-model="vendor.campaignBrowser" :options="browsers" :settings="{ multiple: true }" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-12 control-label mt-2">AdBlock</label>
          <div class="col-sm-12">
            <div class="form-check form-check-inline custom-checkbox">
              <input type="checkbox" class="form-check-input custom-control-input" id="AdBlock" v-model="vendor.campaignExcludeAdBlockUsers">
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
            <textarea name="trackingCode" id="TrackingCode" cols="30" rows="5" class="form-control" v-model="vendor.campaignTrackingCode"></textarea>
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

import 'vue-loading-overlay/dist/vue-loading.css'
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css'

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
      return this.vendor.campaignBudget > 0 && this.vendor.campaignCostPerClick > 0 && this.vendor.campaignObjective && this.vendor.campaignStartDate
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
      languages: [],
      countries: [],
      platforms: ['DESKTOP', 'MOBILE', 'TABLET'],
      operatingSystems: ['Ios', 'MacOs', 'Android', 'Windows'],
      browsers: ['Safari', 'Opera', 'Chrome', 'UCBrowser', 'InApp', 'Samsung', 'Firefox', 'InternetExplorer', 'Edge'],
      scheduleType: 'CONTINUOUSLY'
    }
  },
  methods: {
    preparingData() {
      this.getLanguages()
      this.getCountries()
    },
    getLanguages() {
      this.isLoading = true
      this.languages = []
      axios.get(`/general/languages?provider=outbrain&account=${encodeURIComponent(this.vendor.selectedAccount)}`).then(response => {
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
      axios.get(`/general/countries?provider=outbrain&account=${encodeURIComponent(this.vendor.selectedAccount)}`).then(response => {
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
  }
}
</script>
