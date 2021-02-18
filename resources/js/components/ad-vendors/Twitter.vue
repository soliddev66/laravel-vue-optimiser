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
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">Add Tweet</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Preview</label>
          </div>
          <div class="card-body">
            <form class="row" v-if="selectedProvider && selectedAccount">
              <div class="col" v-if="currentStep == 1">
                <h2>General information</h2>
                <div class="form-group row">
                  <label for="advertiser" class="col-sm-2 control-label mt-2">Advertiser</label>
                  <div class="col-sm-4" v-if="advertisers.length">
                    <select name="advertiser" class="form-control" v-model="selectedAdvertiser" :disabled="instance" @change="selectedAdvertiserChange">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in advertisers" :key="advertiser.id">{{ advertiser.id }} - {{ advertiser.name }}</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row" v-if="selectedAdvertiser">
                  <label for="funding_instrument" class="col-sm-2 control-label mt-2">Funding Instrument</label>
                  <div class="col-lg-10 col-xl-8" v-if="fundingInstruments.length">
                    <select name="funding_instrument" class="form-control" v-model="selectedFundingInstrument" :disabled="instance">
                      <option value="">Select Funding Instrument</option>
                      <option :value="fundingInstrument.id" v-for="fundingInstrument in fundingInstruments" :key="fundingInstrument.id">{{ fundingInstrument.id }} - {{ fundingInstrument.name }}</option>
                    </select>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="entity_status" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'ACTIVE' }">
                        <input type="radio" name="entity_status" id="campaignStatus1" autocomplete="off" value="ACTIVE" v-model="campaignStatus">ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'DRAFT' }">
                        <input type="radio" name="entity_status" id="campaignStatus2" autocomplete="off" value="DRAFT" v-model="campaignStatus">DRAFT
                      </label>
                      <label class="btn bg-olive" :class="{ active: campaignStatus === 'PAUSED' }">
                        <input type="radio" name="entity_status" id="campaignStatus3" autocomplete="off" value="PAUSED" v-model="campaignStatus">PAUSED
                      </label>
                    </div>
                  </div>
                </div>
                <h2>Campaign Setting</h2>
                <div class="form-group row">
                  <label for="start_time" class="col-sm-2 control-label mt-2">Start Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <VueCtkDateTimePicker id="start_time" v-model="campaignStartTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                  <label for="end_time" class="col-sm-2 control-label mt-2">End Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <VueCtkDateTimePicker id="end_time" v-model="campaignEndTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="daily_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Daily Budget Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="daily_budget_amount_local_micro" min="0" class="form-control" v-model="campaignDailyBudgetAmountLocalMicro" />
                  </div>
                  <label for="total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="total_budget_amount_local_micro" min="0" class="form-control" v-model="campaignTotalBudgetAmountLocalMicro" />
                  </div>
                </div>
                <h2>Ad Group</h2>
                <div class="form-group row">
                  <label for="ad_group_name" class="col-sm-2 control-label mt-2">Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="ad_group_name" placeholder="Name" class="form-control" v-model="adGroupName" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_status" class="col-sm-2 control-label mt-2">Status</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'ACTIVE' }">
                        <input type="radio" name="ad_group_status" id="adGroupStatus1" autocomplete="off" value="ACTIVE" v-model="adGroupStatus">ACTIVE
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'DRAFT' }">
                        <input type="radio" name="ad_group_status" id="adGroupStatus2" autocomplete="off" value="DRAFT" v-model="adGroupStatus">DRAFT
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupStatus === 'PAUSED' }">
                        <input type="radio" name="ad_group_status" id="adGroupStatus3" autocomplete="off" value="PAUSED" v-model="adGroupStatus">PAUSED
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_start_time" class="col-sm-2 control-label mt-2">Start Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <VueCtkDateTimePicker id="ad_group_start_time" v-model="adGroupStartTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                  <label for="ad_group_end_time" class="col-sm-2 control-label mt-2">End Time</label>
                  <div class="col-lg-4 col-xl-3">
                    <VueCtkDateTimePicker id="ad_group_end_time" v-model="adGroupEndTime" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :onlyDate="true"></VueCtkDateTimePicker>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_objective" class="col-sm-2 control-label mt-2">Objective</label>
                  <div class="col-lg-10 col-xl-8">
                    <select name="ad_group_objective" class="form-control" v-model="adGroupObjective">
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
                    <select2 id="ad_group_placements" name="ad_group_placements" :options="placements" v-model="adGroupPlacements" :settings="{ multiple: true }"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_advertiser_domain" class="col-sm-2 control-label mt-2">Advertiser Domain</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="ad_group_advertiser_domain" placeholder="Advertiser Domain" class="form-control" v-model="adGroupAdvertiserDomain" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_bid_amount_local_micro" class="col-sm-2 control-label mt-2">Bid Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="ad_group_bid_amount_local_micro" min="0" class="form-control" v-model="adGroupBidAmountLocalMicro" />
                  </div>
                  <label for="ad_group_total_budget_amount_local_micro" class="col-sm-2 control-label mt-2">Total Budget Amount Local Micro</label>
                  <div class="col-lg-4 col-xl-3">
                    <input type="number" name="ad_group_total_budget_amount_local_micro" min="0" class="form-control" v-model="adGroupTotalBudgetAmountLocalMicro" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_categories" class="col-sm-2 control-label mt-2">Category</label>
                  <div class="col-lg-10 col-xl-8">
                    <select2 id="ad_group_categories" name="ad_group_categories" :options="adGroupCategorySelection" v-model="adGroupCategories" :settings="{ multiple: true }"></select2>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_automatically_select_bid" class="col-sm-2 control-label mt-2">Automatically Select Bid</label>
                  <div class="col-lg-4 col-xl-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupAutomaticallySelectBid }">
                        <input type="radio" name="ad_group_automatically_select_bid" id="adGroupAutomaticallySelectBid1" autocomplete="off" :value="true" v-model="adGroupAutomaticallySelectBid">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !adGroupAutomaticallySelectBid }">
                        <input type="radio" name="ad_group_automatically_select_bid" id="adGroupAutomaticallySelectBid2" autocomplete="off" :value="false" v-model="adGroupAutomaticallySelectBid">FALSE
                      </label>
                    </div>
                  </div>
                  <label for="ad_group_bid_type" class="col-sm-2 control-label mt-2">Bid Type</label>
                  <div class="col-lg-4 col-xl-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: adGroupBidType == 'AUTO' && !adGroupBidAmountLocalMicro }">
                        <input type="radio" name="ad_group_bid_type" id="adGroupBidType1" autocomplete="off" value="AUTO" v-model="adGroupBidType">AUTO
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupBidType == 'MAX' && !adGroupBidAmountLocalMicro }">
                        <input type="radio" name="ad_group_bid_type" id="adGroupBidType2" autocomplete="off" value="MAX" v-model="adGroupBidType">MAX
                      </label>
                      <label class="btn bg-olive" :class="{ active: adGroupBidType == 'TARGET' && !adGroupBidAmountLocalMicro }">
                        <input type="radio" name="ad_group_bid_type" id="adGroupBidType3" autocomplete="off" value="TARGET" v-model="adGroupBidType">TARGET
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="ad_group_bid_unit" class="col-sm-2 control-label mt-2">Bid Unit</label>
                  <div class="col-lg-4 col-xl-3">
                    <select name="ad_group_bid_unit" class="form-control" v-model="adGroupBidUnit">
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
                    <select name="ad_group_charge_by" class="form-control" v-model="adGroupChargeBy" disabled>
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
              <div class="col" v-if="currentStep == 2">
                <h2>General information</h2>
                <div class="form-group row" v-if="instance && action == 'edit'">
                  <p class="col-12">
                    Not allow to update the card, please create new one if you want to change the card information. Please note that you must to create new tweet in case create new card as well.
                  </p>
                  <div class="col-sm-2" v-if="saveCard">
                    <button type="button" class="btn btn-primary" @click.prevent="saveCard = !saveCard">Create New</button>
                  </div>
                  <div class="col-sm-2" v-if="!saveCard">
                    <button type="button" class="btn btn-warning" @click.prevent="saveCard = !saveCard">Cancel</button>
                  </div>
                </div>
                <section v-if="action == 'create' || !saveCard">
                  <fieldset class="mb-3 p-3 rounded border" v-for="(card, index) in cards" :key="index">
                    <div class="form-group row">
                      <label for="tweet_text" class="col-sm-2 control-label mt-2">Tweet Text</label>
                      <div class="col-lg-10 col-xl-8">
                        <div class="row mb-2" v-for="(tweetText, indexText) in card.tweetTexts" :key="indexText">
                          <div class="col-sm-8">
                            <input type="text" name="tweet_text" placeholder="Enter texts" class="form-control" v-model="tweetText.text" :disabled="instance && action == 'edit' && saveCard" />
                          </div>
                          <div class="col-sm-4">
                            <button type="button" class="btn btn-light" @click.prevent="removeTweetText(index, indexText)" v-if="indexText > 0"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-primary" @click.prevent="addTweetText(index)" v-if="indexText + 1 == card.tweetTexts.length"><i class="fa fa-plus"></i></button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="tweet_nullcast" class="col-sm-2 control-label mt-2">Tweet Nullcast</label>
                      <div class="col-lg-4 col-xl-3">
                        <div class="btn-group btn-group-toggle">
                          <label class="btn bg-olive" :class="{ active: card.tweetNullcast }">
                            <input type="radio" name="tweet_nullcast" id="tweetNullcast1" autocomplete="off" :value="true" :disabled="instance && saveCard" v-model="card.tweetNullcast">TRUE
                          </label>
                          <label class="btn bg-olive" :class="{ active: !card.tweetNullcast }">
                            <input type="radio" name="tweet_nullcast" id="tweetNullcast2" autocomplete="off" :value="false" :disabled="instance && saveCard" v-model="card.tweetNullcast">FALSE
                          </label>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="card_name" class="col-sm-2 control-label mt-2">Card Name</label>
                      <div class="col-lg-10 col-xl-8">
                        <input type="text" name="card_name" placeholder="Enter a name" class="form-control" v-model="card.name" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="card_media" class="col-sm-2 control-label mt-2">Card Media Image</label>
                      <div class="col-sm-8">
                        <input type="text" name="card_media" placeholder="Media Image" class="form-control" v-model="card.mediaPath" disabled />
                      </div>
                      <div class="col-sm-8 offset-sm-2">
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('cardMedia', index)">Choose File</button>
                      </div>
                      <div class="col-sm-8 offset-sm-2">
                        <small class="text-danger" v-for="(image, indexImage) in card.media" :key="indexImage">
                          <span class="d-inline-block" v-if="image.image && !image.state">Image {{ image.image }} is invalid. A minimum image width of 800px and a width:height aspect ratio of either 1:1 or 1.91:1 is required.</span>
                        </small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="card_website_title" class="col-sm-2 control-label mt-2">Card Website Title</label>
                      <div class="col-lg-10 col-xl-8">
                        <input type="text" name="card_website_title" placeholder="Enter website title" class="form-control" v-model="card.websiteTitle" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="card_website_url" class="col-sm-2 control-label mt-2">Card Website URL</label>
                      <div class="col-lg-10 col-xl-8">
                        <input type="text" name="card_website_url" placeholder="Enter a website URL" class="form-control" v-model="card.websiteUrl" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <p class="col-12" v-if="instance && action == 'edit' && saveCard">
                        Not allow to update the tweet.
                      </p>
                    </div>
                    <div class="row" v-if="index > 0">
                      <div class="col text-right">
                        <button class="btn btn-warning btn-sm" @click.prevent="removeCard(index)">Remove</button>
                      </div>
                    </div>
                  </fieldset>
                  <button class="btn btn-primary btn-sm d-none" @click.prevent="addCard()">Add New</button>
                </section>
              </div>
              <div class="card-body" v-if="currentStep == 3">
                <div class="row">
                  <div class="col-sm-12">
                    <h2>Preview</h2>
                    <div v-html="previewData"></div>
                  </div>
                </div>
              </div>
            </form>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1" :disabled="submitStep1State">Add Card</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2" :disabled="submitStep2State">Submit</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 3">
              <button type="button" class="btn btn-primary">Finish</button>
            </div>
          </div>
        </div>
      </div>
    </div>
    <modal width="60%" height="80%" name="cardMedia">
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
    submitStep1State() {
      return !this.selectedProvider || !this.selectedAccount || !this.selectedAdvertiser || !this.selectedFundingInstrument || !this.campaignName || !this.campaignStartTime || !this.campaignDailyBudgetAmountLocalMicro || !this.adGroupName
    },
    submitStep2State() {
      return ((this.action == 'create' || !this.saveCard) && !this.cardState)
    },
    cardState() {
      for (let i = 0; i < this.cards.length; i++) {
        if (!this.cards[i].name || !this.cards[i].websiteTitle || !this.cards[i].websiteUrl) {
          return false
        }
        for (let j = 0; j < this.cards[i].tweetTexts.length; j++) {
          if (!this.cards[i].tweetTexts[j]) {
            return false
          }
        }

        if (this.cards[i].media.length == 0) {
          return false
        }

        for (let j = 0; j < this.cards[i].media.length; j++) {
          if (!this.cards[i].media[j].image || !this.cards[i].media[j].state) {
            return false
          }
        }
      }
      return true
    }
  },
  mounted() {
    this.currentStep = this.step

    this.loadAdvertisers()

    if (this.instance) {
      this.loadFundingInstruments();
      this.loadAdGroupCategories();
    }

    let vm = this
    this.$root.$on('fm-selected-items', (values) => {
      if (this.openingFileSelector === 'cardMedia') {
        let paths = []
        this.cards[this.fileSelectorIndex].media = []

        for (let i = 0; i < values.length; i++) {
          this.cards[this.fileSelectorIndex].media.push({
            image: values[i].path,
            state: this.validImage(values[i].width, values[i].height)
          })

          paths.push(values[i].path)
        }
        this.cards[this.fileSelectorIndex].mediaPath = paths.join(';')
      }
      vm.$modal.hide(this.openingFileSelector)
    });
  },
  watch: {

  },
  data() {
    let adGroupID = '',
      adGroupName = '';

    if (this.instance) {
      if (this.instance.adGroups.length > 0) {
        adGroupID = this.instance.adGroups[0]['id'];
        adGroupName = this.instance.adGroups[0]['name'];
      }
    }

    return {
      isLoading: false,
      fullPage: true,
      saveCard: true,
      postData: {},
      currentStep: 1,
      redtrackKey: '',
      languages: [],
      countries: [],
      advertisers: [],
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
      actionName: this.action,
      selectedAdvertiser: this.instance ? this.instance.advertiser_id : '',
      selectedFundingInstrument: this.instance ? this.instance.funding_instrument_id : '',
      campaignName: this.instance ? this.instance.name : '',
      campaignStartTime: this.instance && this.instance.start_time ? this.instance.start_time.date.split(' ')[0] : this.$moment().format('YYYY-MM-DD'),
      campaignEndTime: this.instance && this.instance.end_time ? this.instance.end_time.date.split(' ')[0] : '',
      campaignDailyBudgetAmountLocalMicro: this.instance ? this.instance.daily_budget_amount_local_micro / 1e6 : '',
      campaignTotalBudgetAmountLocalMicro: this.instance && this.instance.total_budget_amount_local_micro ? this.instance.total_budget_amount_local_micro / 1e6 : '',
      campaignStatus: this.instance ? this.instance.entity_status : 'PAUSED',
      adGroupID: adGroupID,
      adGroupName: adGroupName,
      adGroupPlacements: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['placements'] : '',
      adGroupObjective: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['objective'] : 'APP_ENGAGEMENTS',
      adGroupAdvertiserDomain: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['advertiser_domain'] : '',
      adGroupBidAmountLocalMicro: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['bid_amount_local_micro'] / 1e6 : '',
      adGroupTotalBudgetAmountLocalMicro: this.instance && this.instance.adGroups.length > 0 && this.instance.adGroups[0]['total_budget_amount_local_micro'] ? this.instance.adGroups[0]['total_budget_amount_local_micro'] / 1e6 : '',
      adGroupCategorySelection: null,
      adGroupCategories: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['categories'] : [],
      adGroupAutomaticallySelectBid: this.instance && this.instance.adGroups.length > 0 && this.instance.adGroups[0]['automatically_select_bid'],
      adGroupBidType: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['bid_type'] : '',
      adGroupStatus: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['entity_status'] : 'PAUSED',
      adGroupBidUnit: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['bid_unit'] : 'LINK_CLICK',
      adGroupChargeBy: this.instance && this.instance.adGroups.length > 0 ? this.instance.adGroups[0]['charge_by'] : 'LINK_CLICK',
      adGroupStartTime: this.instance && this.instance.adGroups.length > 0 && this.instance.adGroups[0]['start_time'] ? this.instance.adGroups[0]['start_time'].date.split(' ')[0] : '',
      adGroupEndTime: this.instance && this.instance.adGroups.length > 0 && this.instance.adGroups[0]['end_time'] ? this.instance.adGroups[0]['end_time'].date.split(' ')[0] : '',
      cards: [{
        name: '',
        media: [],
        mediaPath: '',
        websiteTitle: '',
        websiteUrl: '',
        tweetTexts: [{
          text: ''
        }],
      }],
      tweetText: this.instance && this.action == 'edit' && this.instance.ads.length > 0 ? this.instance.ads[0]['full_text'] : '',
      tweetNullcast: this.instance && this.instance.ads.length > 0 && this.instance.ads[0]['nullcast'],
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      },
      openingFileSelector: '',
      fileSelectorIndex: 0,
      previewData: '',
    }
  },
  methods: {
    validImage(width, height)  {
      return width >= 800 && (width / height == 1 || width / height == 1.91)
    },
    selectedAdvertiserChange() {
      this.loadFundingInstruments();
      this.loadAdGroupCategories();
    },
    openChooseFile(name, index = 0) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show(name)
    },
    loadAdvertisers() {
      this.isLoading = true
      axios.get(`/account/advertisers?provider=${this.selectedProvider}&account=${this.selectedAccount}`).then(response => {
        this.advertisers = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    loadFundingInstruments() {
      this.isLoading = true
      axios.get(`/account/funding-instruments?provider=${this.selectedProvider}&account=${this.selectedAccount}&advertiser=${this.selectedAdvertiser}`).then(response => {
        this.fundingInstruments = response.data
      }).catch(err => {}).finally(() => {
        this.isLoading = false
      })
    },
    loadAdGroupCategories() {
      this.isLoading = true
      axios.get(`/account/ad-group-categories?provider=${this.selectedProvider}&account=${this.selectedAccount}&advertiser=${this.selectedAdvertiser}`).then(response => {
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
    addCard() {
      this.cards.push({
        name: '',
        media: [],
        mediaPath: '',
        websiteTitle: '',
        websiteUrl: '',
        tweetTexts: [{
          text: ''
        }],
        tweetNullcast: ''
      })
    },
    removeCard(index) {
      this.cards.splice(index, 1);
    },
    addTweetText(index) {
      this.cards[index].tweetTexts.push({ text: '' })
    },
    removeTweetText(index, indexText) {
      this.cards[index].tweetTexts.splice(indexText, 1)
    },
    submitStep1() {
      const step1Data = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        advertiser: this.selectedAdvertiser,
        fundingInstrument: this.selectedFundingInstrument,
        campaignName: this.campaignName,
        campaignStartTime: this.campaignStartTime,
        campaignEndTime: this.campaignEndTime,
        campaignDailyBudgetAmountLocalMicro: this.campaignDailyBudgetAmountLocalMicro,
        campaignTotalBudgetAmountLocalMicro: this.campaignTotalBudgetAmountLocalMicro,
        campaignStatus: this.campaignStatus,
        adGroupID: this.adGroupID,
        adGroupName: this.adGroupName,
        adGroupObjective: this.adGroupObjective,
        adGroupPlacements: this.adGroupPlacements,
        adGroupAdvertiserDomain: this.adGroupAdvertiserDomain,
        adGroupCategories: this.adGroupCategories,
        adGroupAutomaticallySelectBid: this.adGroupAutomaticallySelectBid,
        adGroupBidType: this.adGroupBidType,
        adGroupBidUnit: this.adGroupBidUnit,
        adGroupChargeBy: this.adGroupChargeBy,
        adGroupStartTime: this.adGroupStartTime,
        adGroupEndTime: this.adGroupEndTime,
        adGroupStatus: this.adGroupStatus,
        adGroupBidAmountLocalMicro: this.adGroupBidAmountLocalMicro,
        adGroupTotalBudgetAmountLocalMicro: this.adGroupTotalBudgetAmountLocalMicro,
      }
      this.postData = {...this.postData, ...step1Data }
      this.currentStep = 2
    },
    submitStep2() {
      const step2Data = {
        cards: this.cards,
        saveCard: this.saveCard
      }
      this.postData = {...this.postData, ...step2Data }

      this.isLoading = true
      let url = '/campaigns';

      if (this.action == 'edit') {
        url += '/update/' + this.instance.instance_id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.currentStep = 3
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = '/campaigns';
          });
        }
      }).catch(error => {}).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>
