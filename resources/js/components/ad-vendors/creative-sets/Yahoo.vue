<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row">
      <div class="col">
        <h1>Yahoo Gemini</h1>
        <div class="form-group row">
          <label for="type" class="col-sm-2 control-label mt-2">Type</label>
          <div class="col-sm-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: vendor.campaignType === 'NATIVE' }">
                <input type="radio" name="type" id="campaignType1" autocomplete="off" value="NATIVE" v-model="vendor.campaignType"> Native Only
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignType === 'SEARCH' }">
                <input type="radio" name="type" id="campaignType2" autocomplete="off" value="SEARCH" v-model="vendor.campaignType"> Search Only
              </label>
              <label class="btn bg-olive" :class="{ active: vendor.campaignType === 'SEARCH_AND_NATIVE' }">
                <input type="radio" name="type" id="campaignType3" autocomplete="off" value="SEARCH_AND_NATIVE" v-model="vendor.campaignType"> Search and Native
              </label>
            </div>
          </div>
        </div>
        <h2>Define your audience</h2>
        <div class="form-group row">
          <label for="language" class="col-sm-2 control-label mt-2">Language</label>
          <div class="col-sm-8">
            <select2 id="language" name="language" :options="languages" v-model="vendor.campaignLanguage"></select2>
          </div>
        </div>
        <div class="form-group row">
          <label for="location" class="col-sm-2 control-label mt-2">Location</label>
          <div class="col-sm-8">
            <select2 id="location" name="location" v-model="vendor.campaignLocation" :options="countries" :settings="{ multiple: true }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="gender" class="col-sm-2 control-label mt-2">Gender</label>
          <div class="col-sm-8">
            <select2 id="gender" name="gender" v-model="vendor.campaignGender" :options="genders" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="age" class="col-sm-2 control-label mt-2">Age</label>
          <div class="col-sm-8">
            <select2 id="age" name="age" v-model="vendor.campaignAge" :options="ages" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <div class="form-group row">
          <label for="device" class="col-sm-2 control-label mt-2">Device</label>
          <div class="col-sm-8">
            <select2 name="device" v-model="vendor.campaignDevice" :options="devices" :settings="{ multiple: true, placeholder: 'ALL' }" />
          </div>
        </div>
        <h2>Campaign settings</h2>
        <div class="form-group row">
          <label for="objective" class="col-sm-2 control-label mt-2">Objective</label>
          <div class="col-sm-8">
            <select name="objective" class="form-control" v-model="vendor.campaignObjective">
              <option value="VISIT_WEB">Visit Web</option>
              <option value="VISIT_OFFER">Visit Offer</option>
              <option value="PROMOTE_BRAND">Promote Brand</option>
              <option value="INSTALL_APP">Install App</option>
              <option value="MAIL_SPONSORED">Mail Sponsored</option>
              <option value="REENGAGE_APP">Reengage App</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="budget" class="col-sm-2 control-label mt-2">Budget</label>
          <div class="col-sm-2">
            <input type="number" name="budget" min="40" class="form-control" v-model="vendor.campaignBudget" />
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
          <label for="bid_strategy" class="col-sm-2 control-label mt-2">Bid Strategy</label>
          <div class="col-sm-8">
            <select name="bid_strategy" class="form-control" v-model="vendor.campaignStrategy">
              <option value="OPT_ENHANCED_CPC">Enhanced CPC</option>
              <option value="OPT_POST_INSTALL">Post Install</option>
              <option value="OPT_CONVERSION">Conversion</option>
              <option value="OPT_CLICK">Click</option>
              <option value="MAX_OPT_CONVERSION">Max Conversion</option>
            </select>
          </div>
        </div>
        <div class="form-group row">
          <label for="bid_adjustment" class="col-sm-2 control-label mt-2">Native Bid Adjustment</label>
          <div class="col-sm-8">
            <div class="form-group row">
              <div class="col">
                <treeselect :options="supportedSites" :disable-branch-nodes="true" @select="supportedSiteChanged" placeholder="Add publisher..." />
              </div>
            </div>
            <div class="form-group row">
              <div class="col">
                <select2 id="network_setting" name="network_setting" v-model="vendor.networkSetting" :options="networkSettings" @change="networkSettingChanged" placeholder="Load from setting..." />
              </div>
            </div>
            <div class="row">
              <div class="col">
                <div class="row">
                  <label class="col-sm-4 mt-2">Publishers</label>
                  <label class="col-sm-8 mt-2 text-center">Bid Adjustments</label>
                </div>
                <div class="row">
                  <label for="bid_adjustment_group_1a" class="col-sm-5 control-label mt-2">Group 1A <small>(+800%)</small></label>
                  <select class="form-control col-sm-2">
                    <option value="1">Increase By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_group_1a" min="0" max="800" class="form-control" v-model="vendor.campaignSupplyGroup1A" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <label for="bid_adjustment_group_1b" class="col-sm-5 control-label mt-2">Group 1B <small>(+600% — -40%)</small></label>
                  <select class="form-control col-sm-2" v-model="vendor.incrementType1b">
                    <option value="1">Increase By</option>
                    <option value="-1">Decrease By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_group_1b" min="0" max="600" class="form-control" v-model="vendor.campaignSupplyGroup1B" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <label for="bid_adjustment_group_2a" class="col-sm-5 control-label mt-2">Group 2A <small>(+800% — -30%)</small></label>
                  <select class="form-control col-sm-2" v-model="vendor.incrementType2a">
                    <option value="1">Increase By</option>
                    <option value="-1">Decrease By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_group_2a" min="0" max="800" class="form-control" v-model="vendor.campaignSupplyGroup2A" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <label for="bid_adjustment_group_2b" class="col-sm-5 control-label mt-2">Group 2B <small>(+600% — -70%)</small></label>
                  <select class="form-control col-sm-2" v-model="vendor.incrementType2b">
                    <option value="1">Increase By</option>
                    <option value="-1">Decrease By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_group_2b" min="0" max="600" class="form-control" v-model="vendor.campaignSupplyGroup2B" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <label for="bid_adjustment_group_3a" class="col-sm-5 control-label mt-2">Group 3A <small>(+800% — -50%)</small></label>
                  <select class="form-control col-sm-2" v-model="vendor.incrementType3a">
                    <option value="1">Increase By</option>
                    <option value="-1">Decrease By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_group_3a" min="0" max="800" class="form-control" v-model="vendor.campaignSupplyGroup3A" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="row">
                  <label for="bid_adjustment_group_3b" class="col-sm-5 control-label mt-2">Group 3B <small>(+600% — -80%)</small></label>
                  <select class="form-control col-sm-2" v-model="vendor.incrementType3b">
                    <option value="1">Increase By</option>
                    <option value="-1">Decrease By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_group_3b" min="0" max="600" class="form-control" v-model="vendor.campaignSupplyGroup3B" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                </div>
                <div class="row" v-for="(supportedSiteItem, index) in supportedSiteCollections" :key="index">
                  <label for="bid_adjustment_site_group" class="col-sm-5 control-label mt-2">{{ supportedSiteItem.label }} <small>{{ supportedSiteItem.subLabel }}</small></label>
                  <select class="form-control col-sm-2" v-model="vendor.supportedSiteItem.incrementType">
                    <option value="1">Increase By</option>
                    <option value="-1">Decrease By</option>
                  </select>
                  <div class="input-group col-sm-3 mb-1">
                    <input type="number" name="bid_adjustment_site_group" min="0" max="800" class="form-control" v-model="vendor.supportedSiteItem.bidModifier" />
                    <div class="input-group-append">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                  <div class="col-sm-2">
                    <button class="btn btn-primary" @click.prevent="removeSupportedSite(index)">Remove</button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="site_block" class="col-sm-2 control-label mt-2">Site blocks</label>
          <div class="col-sm-8">
            <div class="row">
              <div class="col">
                <textarea class="form-control" rows="3" placeholder="Enter site block" v-model="vendor.campaignSiteBlock"></textarea>
                <small>Separate sites by break new line</small>
              </div>
            </div>
            <div class="row mt-2">
              <div class="col-sm-4" v-if="!saveNetworkSetting">
                <input type="text" name="network_setting_name" v-model="vendor.networkSettingName" class="form-control" placeholder="Enter setting name">
              </div>
              <div class="col-sm-5" v-if="saveNetworkSetting && campaignSupplyGroupState">
                <button type="button" class="btn btn-primary" @click.prevent="saveNetworkSetting = !saveNetworkSetting">Save these setting</button>
              </div>
              <div class="col-sm-3">
                <button type="button" v-if="!saveNetworkSetting && networkSettingName && campaignSupplyGroupState" class="btn btn-success" @click.prevent="storeNetworkSetting()">Save</button>
                <button type="button" v-if="!saveNetworkSetting" class="btn btn-warning" @click.prevent="saveNetworkSetting = !saveNetworkSetting">Cancel</button>
              </div>
            </div>
          </div>
        </div>
        <div class="form-group row">
          <label for="conversion_counting" class="col-sm-2 control-label mt-2">Conversion counting</label>
          <div class="col-sm-8">
            <select name="conversion_counting" class="form-control" v-model="vendor.campaignConversionCounting">
              <option value="ALL_PER_INTERACTION">All per interaction</option>
              <option value="ONE_PER_INTERACTION">One per interaction</option>
            </select>
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
          <label for="bid_strategy" class="col-sm-2 control-label mt-2">Bid strategy</label>
          <div class="col-sm-8">
            <p>{{ vendor.campaignStrategy }}</p>
          </div>
        </div>
        <div class="form-group row">
          <label for="bid_cpc" class="col-sm-2 control-label mt-2">Bid (CPC)</label>
          <div class="col-sm-8">
            <input type="number" name="bid_cpc" min="1" class="form-control" v-model="vendor.bidAmount" />
          </div>
        </div>
        <div class="form-group row">
          <label for="schedule" class="col-sm-2 control-label mt-2">Schedule</label>
          <div class="col-sm-8">
            <div class="btn-group btn-group-toggle">
              <label class="btn bg-olive" :class="{ active: scheduleType === 'IMMEDIATELY' }">
                <input type="radio" name="schedule" id="scheduleType1" autocomplete="off" value="IMMEDIATELY" v-model="vendor.scheduleType"> Start running ads immediately
              </label>
              <label class="btn bg-olive" :class="{ active: scheduleType === 'CUSTOM' }">
                <input type="radio" name="schedule" id="scheduleType2" autocomplete="off" value="CUSTOM" v-model="vendor.scheduleType"> Set a start and end date
              </label>
            </div>
          </div>
        </div>
        <div class="form-group row" v-if="scheduleType === 'CUSTOM'">
          <label for="start_date" class="col-sm-2 control-label mt-2">Start Date</label>
          <div class="col-sm-4">
            <VueCtkDateTimePicker id="start_date" v-model="vendor.campaignStartDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
          </div>
          <label for="end_date" class="col-sm-2 control-label mt-2">End Date</label>
          <div class="col-sm-4">
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

import Treeselect from '@riophae/vue-treeselect'

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
    Treeselect
  },
  computed: {

  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {
    //
  },
  data() {
    Object.assign(this.vendor, {
      selectedAccount: '4PYGHZXE65D5HWTH7MNJQEPOPE', // temp
      campaignType: 'NATIVE',
      campaignLanguage: 'en',
      campaignStrategy: 'OPT_CLICK',
    })

    return {
      isLoading: false,
      fullPage: true,
      languages: [],
      countries: [],
      genders: [],
      ages: [],
      devices: [],
      supportedSites: [],
      networkSettings: [],
      saveNetworkSetting: true,
      supportedSiteCollections: [],
      scheduleType: 'IMMEDIATELY',
      campaignSupplyGroupState: true

    }
  },
  methods: {
    preparingData() {
      this.getLanguages()
    },

    getLanguages() {
      this.isLoading = true
      this.languages = []
      axios.get(`/general/languages?provider=yahoo&account=${this.vendor.selectedAccount}`).then(response => {
        if (response.data) {
          this.languages = response.data.map(language => {
            return {
              id: language.value || language.code,
              text: language.name ? language.name.toUpperCase() : language.value
            }
          })
        }
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },

    supportedSiteChanged(node, instanceId) {
      this.supportedSiteCollections.push({
        label: node.label,
        subLabel: '(+800% — -80%)',
        key: node.id,
        type: node.type,
        incrementType: 1,
        bidModifier: ''
      })
    },

    networkSettingChanged() {
      let data = this.networkSettingData[this.networkSetting]
      this.campaignSupplyGroup1A = data.group_1a
      this.campaignSupplyGroup1B = data.group_1b
      this.campaignSupplyGroup2A = data.group_2a
      this.campaignSupplyGroup2B = data.group_2b
      this.campaignSupplyGroup3A = data.group_3a
      this.campaignSupplyGroup3B = data.group_3b

      if (this.campaignSupplyGroup1B < 0) {
        this.incrementType1b = -1
        this.campaignSupplyGroup1B = -this.campaignSupplyGroup1B
      }

      if (this.campaignSupplyGroup2A < 0) {
        this.incrementType2a = -1
        this.campaignSupplyGroup2A = -this.campaignSupplyGroup2A
      }

      if (this.campaignSupplyGroup2B < 0) {
        this.incrementType2b = -1
        this.campaignSupplyGroup2B = -this.campaignSupplyGroup2B
      }

      if (this.campaignSupplyGroup3A < 0) {
        this.incrementType3a = -1
        this.campaignSupplyGroup3A = -this.campaignSupplyGroup3A
      }

      if (this.campaignSupplyGroup3B < 0) {
        this.incrementType3b = -1
        this.campaignSupplyGroup3B = -this.campaignSupplyGroup3B
      }

      this.campaignSiteBlock = data.site_block

      if (data.site_group) {
        this.supportedSiteCollections = JSON.parse(data.site_group)

        for (let i = 0; i < this.supportedSiteCollections.length; i++) {
          if (this.supportedSiteCollections[i].bidModifier < 0) {
            this.supportedSiteCollections[i].bidModifier = -this.supportedSiteCollections[i].bidModifier
          }
        }
      }
    },
  }
}
</script>
