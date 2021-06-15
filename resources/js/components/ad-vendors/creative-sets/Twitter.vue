<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row">
      <div class="col">
        <h1 class="mb-2">Twitter</h1>

        <div class="form-group row">
          <label for="funding_instrument" class="col-sm-2 control-label mt-2">Funding Instrument</label>
          <div class="col-lg-10 col-xl-8" v-if="fundingInstruments.length">
            <select name="funding_instrument" class="form-control" v-model="vendor.selectedFundingInstrument">
              <option value="">Select Funding Instrument</option>
              <option :value="fundingInstrument.id" v-for="fundingInstrument in fundingInstruments" :key="fundingInstrument.id">{{ fundingInstrument.id }} - {{ fundingInstrument.name }}</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="entity_status" class="col-sm-2 control-label mt-2">Status</label>
          <div class="col-lg-10 col-xl-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignStatus === 'ACTIVE' }">
                <input type="radio" name="entity_status" id="campaignStatus1" autocomplete="off" value="ACTIVE" v-model="vendor.campaignStatus">ACTIVE
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignStatus === 'DRAFT' }">
                <input type="radio" name="entity_status" id="campaignStatus2" autocomplete="off" value="DRAFT" v-model="vendor.campaignStatus">DRAFT
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignStatus === 'PAUSED' }">
                <input type="radio" name="entity_status" id="campaignStatus3" autocomplete="off" value="PAUSED" v-model="vendor.campaignStatus">PAUSED
              </label>
            </div>
          </div>
        </div>
        <h2>Campaign Setting</h2>
        <div class="form-group row">
          <label for="start_time" class="col-sm-2 control-label mt-2">Start Time</label>
          <div class="col-lg-4 col-xl-3">
            <VueCtkDateTimePicker id="start_time" v-model="vendor.campaignStartTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
          </div>
          <label for="end_time" class="col-sm-2 control-label mt-2">End Time</label>
          <div class="col-lg-4 col-xl-3">
            <VueCtkDateTimePicker id="end_time" v-model="vendor.campaignEndTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
          </div>
        </div>
        <div class="form-group row">
          <label for="daily_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Daily Budget Amount Local Micro</label>
          <div class="col-lg-4 col-xl-3">
            <input type="number" name="daily_budget_amount_local_micro" min="0" class="form-control" v-model="vendor.campaignDailyBudgetAmountLocalMicro" />
          </div>
          <label for="total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
          <div class="col-lg-4 col-xl-3">
            <input type="number" name="total_budget_amount_local_micro" min="0" class="form-control" v-model="vendor.campaignTotalBudgetAmountLocalMicro" />
          </div>
        </div>
        <h2>Ad Group</h2>
        <div class="form-group row">
          <label for="ad_group_name" class="col-sm-2 control-label mt-2">Name</label>
          <div class="col-lg-10 col-xl-8">
            <input type="text" name="ad_group_name" placeholder="Name" class="form-control" v-model="vendor.adGroupName" />
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_status" class="col-sm-2 control-label mt-2">Status</label>
          <div class="col-lg-10 col-xl-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.adGroupStatus === 'ACTIVE' }">
                <input type="radio" name="ad_group_status" id="adGroupStatus1" autocomplete="off" value="ACTIVE" v-model="vendor.adGroupStatus">ACTIVE
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.adGroupStatus === 'DRAFT' }">
                <input type="radio" name="ad_group_status" id="adGroupStatus2" autocomplete="off" value="DRAFT" v-model="vendor.adGroupStatus">DRAFT
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.adGroupStatus === 'PAUSED' }">
                <input type="radio" name="ad_group_status" id="adGroupStatus3" autocomplete="off" value="PAUSED" v-model="vendor.adGroupStatus">PAUSED
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_start_time" class="col-sm-2 control-label mt-2">Start Time</label>
          <div class="col-lg-4 col-xl-3">
            <VueCtkDateTimePicker id="ad_group_start_time" v-model="vendor.adGroupStartTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
          </div>
          <label for="ad_group_end_time" class="col-sm-2 control-label mt-2">End Time</label>
          <div class="col-lg-4 col-xl-3">
            <VueCtkDateTimePicker id="ad_group_end_time" v-model="vendor.adGroupEndTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_objective" class="col-sm-2 control-label mt-2">Objective</label>
          <div class="col-lg-10 col-xl-8">
            <select name="ad_group_objective" class="form-control" v-model="vendor.adGroupObjective">
              <option value="APP_ENGAGEMENTS">APP_ENGAGEMENTS</option>
              <option value="APP_INSTALLS">APP_INSTALLS</option>
              <option value="REACH">REACH</option>
              <option value="FOLLOWERS">FOLLOWERS</option>
              <option value="ENGAGEMENTS">ENGAGEMENTS</option>
              <option value="VIDEO_VIEWS">VIDEO_VIEWS</option>
              <option value="PREROLL_VIEWS">PREROLL_VIEWS</option>
              <option value="WEBSITE_CLICKS">WEBSITE_CLICKS</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_placements" class="col-sm-2 control-label mt-2">Placements</label>
          <div class="col-lg-10 col-xl-8">
            <select2 id="ad_group_placements" name="ad_group_placements" :options="placements" v-model="vendor.adGroupPlacements" :settings="{ multiple: true }"></select2>
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_advertiser_domain" class="col-sm-2 control-label mt-2">Advertiser Domain</label>
          <div class="col-lg-10 col-xl-8">
            <input type="text" name="ad_group_advertiser_domain" placeholder="Advertiser Domain" class="form-control" v-model="vendor.adGroupAdvertiserDomain" />
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_bid_amount_local_micro" class="col-sm-2 control-label mt-2">Bid Amount Local Micro</label>
          <div class="col-lg-4 col-xl-3">
            <input type="number" name="ad_group_bid_amount_local_micro" min="0" class="form-control" v-model="vendor.adGroupBidAmountLocalMicro" />
          </div>
          <label for="ad_group_total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
          <div class="col-lg-4 col-xl-3">
            <input type="number" name="ad_group_total_budget_amount_local_micro" min="0" class="form-control" v-model="vendor.adGroupTotalBudgetAmountLocalMicro" />
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_categories" class="col-sm-2 control-label mt-2">Category</label>
          <div class="col-lg-10 col-xl-8">
            <select2 id="ad_group_categories" name="ad_group_categories" :options="adGroupCategorySelection" v-model="vendor.adGroupCategories" :settings="{ multiple: true }"></select2>
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_automatically_select_bid" class="col-sm-2 control-label mt-2">Automatically Select Bid</label>
          <div class="col-lg-4 col-xl-3">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.adGroupAutomaticallySelectBid }">
                <input type="radio" name="ad_group_automatically_select_bid" id="adGroupAutomaticallySelectBid1" autocomplete="off" :value="true" v-model="vendor.adGroupAutomaticallySelectBid">TRUE
              </label>
              <label class="btn bg-olive" :class="{ active: !vendor.adGroupAutomaticallySelectBid }">
                <input type="radio" name="ad_group_automatically_select_bid" id="adGroupAutomaticallySelectBid2" autocomplete="off" :value="false" v-model="vendor.adGroupAutomaticallySelectBid">FALSE
              </label>
            </div>
          </div>
          <label for="ad_group_bid_type" class="col-sm-2 control-label mt-2">Bid Type</label>
          <div class="col-lg-4 col-xl-3">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.adGroupBidType == 'AUTO' && !vendor.adGroupBidAmountLocalMicro }">
                <input type="radio" name="ad_group_bid_type" id="adGroupBidType1" autocomplete="off" value="AUTO" v-model="vendor.adGroupBidType">AUTO
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.adGroupBidType == 'MAX' && !vendor.adGroupBidAmountLocalMicro }">
                <input type="radio" name="ad_group_bid_type" id="adGroupBidType2" autocomplete="off" value="MAX" v-model="vendor.adGroupBidType">MAX
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.adGroupBidType == 'TARGET' && !vendor.adGroupBidAmountLocalMicro }">
                <input type="radio" name="ad_group_bid_type" id="adGroupBidType3" autocomplete="off" value="TARGET" v-model="vendor.adGroupBidType">TARGET
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="ad_group_bid_unit" class="col-sm-2 control-label mt-2">Bid Unit</label>
          <div class="col-lg-4 col-xl-3">
            <select name="ad_group_bid_unit" class="form-control" v-model="vendor.adGroupBidUnit">
              <option value="LINK_CLICK">LINK_CLICK</option>
              <option value="APP_CLICK">APP_CLICK</option>
              <option value="APP_INSTALL">APP_INSTALL</option>
              <option value="VIEW">VIEW</option>
              <option value="VIEW_3S_100PCT">VIEW_3S_100PCT</option>
              <option value="VIEW_6S">VIEW_6S</option>
            </select>
          </div>
          <label for="ad_group_charge_by" class="col-sm-2 control-label mt-2">Charge By</label>
          <div class="col-lg-4 col-xl-3">
            <select name="ad_group_charge_by" class="form-control" v-model="vendor.adGroupChargeBy" disabled>
              <option value="LINK_CLICK">LINK_CLICK</option>
              <option value="APP_CLICK">APP_CLICK</option>
              <option value="APP_INSTALL">APP_INSTALL</option>
              <option value="VIEW">VIEW</option>
              <option value="VIEW_3S_100PCT">VIEW_3S_100PCT</option>
              <option value="VIEW_6S">VIEW_6S</option>
            </select>
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
      return this.vendor.selectedFundingInstrument && this.vendor.campaignStartTime && this.vendor.campaignDailyBudgetAmountLocalMicro && this.vendor.adGroupName
    },
  },
  mounted() {
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
      fundingInstruments: [],
      placements: [
        { id: 'ALL_ON_TWITTER', text: 'ALL_ON_TWITTER' },
        { id: 'PUBLISHER_NETWORK', text: 'PUBLISHER_NETWORK' },
        { id: 'TAP_BANNER', text: 'TAP_BANNER' },
        { id: 'TAP_FULL', text: 'TAP_FULL' },
        { id: 'TAP_FULL_LANDSCAPE', text: 'TAP_FULL_LANDSCAPE' },
        { id: 'TAP_NATIVE', text: 'TAP_NATIVE' },
        { id: 'TAP_MRECT', text: 'TAP_MRECT' },
        { id: 'TWITTER_PROFILE', text: 'TWITTER_PROFILE' },
        { id: 'TWITTER_SEARCH', text: 'TWITTER_SEARCH' },
        { id: 'TWITTER_TIMELINE', text: 'TWITTER_TIMELINE' }
      ],
      adGroupCategorySelection: null
    }
  },
  methods: {
    preparingData() {
      this.loadFundingInstruments()
      this.loadAdGroupCategories()
    },
    loadFundingInstruments() {
      this.isLoading = true
      axios.get(`/account/funding-instruments?provider=twitter&account=${this.vendor.selectedAccount}&advertiser=${this.vendor.selectedAdvertiser}`).then(response => {
        this.fundingInstruments = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    loadAdGroupCategories() {
      this.isLoading = true
      axios.get(`/account/ad-group-categories?provider=twitter&account=${this.vendor.selectedAccount}&advertiser=${this.vendor.selectedAdvertiser}`).then(response => {
        this.adGroupCategorySelection = response.data.map(category => {
          return {
            id: category.id,
            text: category.name
          }
        })
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
  }
}
</script>
