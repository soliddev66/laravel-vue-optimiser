<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>

    <div class="row">
      <div class="col">
        <h1 class="mb-2">Yahoo Japan</h1>
        <div class="form-group row">
          <label for="name" class="col-sm-2 control-label mt-2">Campaign Goal</label>
          <div class="col-sm-8">
            <select2 name="campaign_campaign_bid_strategy" :options="campaignGoals" v-model="vendor.campaignGoal" placeholder="Select campaign goal"></select2>
          </div>
        </div>
        <div class="form-group row">
          <label for="type" class="col-sm-2 control-label mt-2">Status</label>
          <div class="col-sm-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignStatus === 'ACTIVE' }">
                <input type="radio" name="type" id="campaignStatus1" autocomplete="off" value="ACTIVE" v-model="vendor.campaignStatus"> ACTIVE
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignStatus === 'PAUSED' }">
                <input type="radio" name="type" id="campaignStatus2" autocomplete="off" value="PAUSED" v-model="vendor.campaignStatus"> PAUSED
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="budget" class="col-sm-2 control-label mt-2">Budgets</label>
          <div class="col-sm-8">
            <input type="number" name="budget" min="40" class="form-control" v-model="vendor.campaignBudget" />
          </div>
        </div>
        <div class="form-group row">
          <label for="budget" class="col-sm-2 control-label mt-2">Budget Delivery</label>
          <div class="col-sm-4">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignBudgetDeliveryMethod === 'STANDARD' }">
                <input type="radio" name="type" id="campaignBudgetDeliveryMethod1" autocomplete="off" value="STANDARD" v-model="vendor.campaignBudgetDeliveryMethod"> STANDARD
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignBudgetDeliveryMethod === 'ACCELERATED' }">
                <input type="radio" name="type" id="campaignBudgetDeliveryMethod2" autocomplete="off" value="ACCELERATED" v-model="vendor.campaignBudgetDeliveryMethod"> ACCELERATED
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignBudgetDeliveryMethod === 'UNKNOWN' }">
                <input type="radio" name="type" id="campaignBudgetDeliveryMethod3" autocomplete="off" value="UNKNOWN" v-model="vendor.campaignBudgetDeliveryMethod"> UNKNOWN
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 control-label mt-2">Campaign Bid Strategy</label>
          <div class="col-sm-8">
            <select2 name="campaign_campaign_bid_strategy" :options="campaignCampaignBidStrategies" v-model="vendor.campaignCampaignBidStrategy"></select2>
          </div>
        </div>
        <div class="form-group row" v-if="vendor.campaignCampaignBidStrategy == 'MAX_CPC'">
          <label for="name" class="col-sm-2 control-label mt-2">Max Bid Of Campaign (CPC)</label>
          <div class="col-sm-8">
            <input type="text" name="campaign_max_cpc_bid_value" placeholder="Max Bid Of Campaign (CPC)" class="form-control" v-model="vendor.campaignMaxCpcBidValue" />
          </div>
        </div>
        <div class="form-group row" v-if="vendor.campaignCampaignBidStrategy == 'MAX_CPV'">
          <label for="name" class="col-sm-2 control-label mt-2">Max bid of campaign (CPV)</label>
          <div class="col-sm-8">
            <input type="text" name="campaign_max_cpv_bid_value" placeholder="Max bid of campaign (CPV)" class="form-control" v-model="vendor.campaignMaxCpvBidValue" />
          </div>
        </div>
        <div class="form-group row" v-if="vendor.campaignCampaignBidStrategy == 'MAX_VCPM'">
          <label for="name" class="col-sm-2 control-label mt-2">Max bid of campaign (vCPM)</label>
          <div class="col-sm-8">
            <input type="text" name="campaign_max_vcpm_bid_value" placeholder="Max bid of campaign (vCPM)" class="form-control" v-model="vendor.campaignMaxVcpmBidValue" />
          </div>
        </div>
        <div class="form-group row" v-if="vendor.campaignCampaignBidStrategy == 'TARGET_CPA'">
          <label for="name" class="col-sm-2 control-label mt-2">Target bid of campaign (tCPA)</label>
          <div class="col-sm-8">
            <input type="text" name="campaign_Target_cpa_bid_value" placeholder="Target bid of campaign (tCPA)" class="form-control" v-model="vendor.campaignTargetCpaBidValue" />
          </div>
        </div>
        <div class="form-group row">
          <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
          <div class="col-sm-3">
            <VueCtkDateTimePicker id="start_date" v-model="vendor.campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true" :minDate="$moment().add(1, 'days').format('YYYY-MM-DD')"></VueCtkDateTimePicker>
          </div>
          <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
          <div class="col-sm-3">
            <VueCtkDateTimePicker id="end_date" v-model="vendor.campaignEndDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true" :minDate="$moment().add(1, 'days').format('YYYY-MM-DD')"></VueCtkDateTimePicker>
          </div>
        </div>
        <h2>Create group</h2>
        <div class="form-group row">
          <label for="ad_group_name" class="col-sm-2 control-label mt-2">Ad group name</label>
          <div class="col-sm-8">
            <input type="text" name="ad_group_name" class="form-control" v-model="vendor.adGroupName" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 control-label mt-2">Gender</label>
          <div class="col-sm-8">
            <select2 name="gender" v-model="vendor.campaignGenders" :options="genders" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 control-label mt-2">Age</label>
          <div class="col-sm-8">
            <select2 name="age" v-model="vendor.campaignAges" :options="ages" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 control-label mt-2">Device</label>
          <div class="col-sm-8">
            <select2 name="device" v-model="vendor.campaignDevices" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 control-label mt-2">Device App</label>
          <div class="col-sm-8">
            <select2 name="device_app" v-model="vendor.campaignDeviceApps" :options="deviceApps" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label class="col-sm-2 control-label mt-2">Device Os</label>
          <div class="col-sm-8">
            <select2 name="device_os" v-model="vendor.campaignDeviceOs" :options="deviceOs" :settings="{ multiple: true, placeholder: 'ALL' }" />
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
      return this.vendor.campaignGoal && this.vendor.campaignBudget > 0 && this.vendor.campaignStartDate && this.vendor.adGroupName
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
      campaignGoals: [],
    }
  },
  methods: {
    preparingData() {
      this.getCampaignGoals()
    },

    getCampaignGoals() {
      this.isLoading = true
      axios.get(`/general/campaign-goals?provider=yahoojp&account=${this.vendor.selectedAccount}&advertiser=${this.vendor.selectedAdvertiser}`).then(response => {
        this.campaignGoals = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
  }
}
</script>
