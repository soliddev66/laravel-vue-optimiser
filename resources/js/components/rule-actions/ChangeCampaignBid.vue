<template>
  <div class="row">
    <div class="col">
      <div class="vld-parent">
        <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
      </div>
      <fieldset class="mb-3 p-3 rounded border" v-for="(ruleCampaign, index) in ruleCampaigns" :key="index">
        <div class="row">
          <div class="col-sm-11">
            <div class="form-group row">
              <label for="" class="col-sm-2 control-label">Campaign</label>
              <div class="col-sm-10">
                <select2 name="campaigns" v-model="ruleCampaign.id" :options="campaignSelections" @change="campaignSelected(index, ruleCampaign.id)" />
              </div>
            </div>

            <div class="row" v-if="ruleCampaign.provider_id == 1">
              <div class="col">
                <fieldset class="mb-3 p-3 rounded border" v-for="(campaignAdGroup, indexAdGroup) in ruleCampaign.adGroups" :key="indexAdGroup">
                  <div class="form-group row">
                    <label for="rule_campaign_bid" class="col-sm-2 control-label">Ad Group</label>
                    <div class="col-sm-9">
                      <select2 name="campaign_ad_group" v-model="campaignAdGroup.id" :options="ruleCampaign.adGroupSelections" />
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="rule_campaign_bid" class="col-sm-2 control-label">Bid</label>
                    <div class="col-sm-9">
                      <input type="text" name="rule_campaign_bid" v-model="campaignAdGroup.data.bid" class="form-control" placeholder="Enter bid">
                    </div>
                    <div class="col-sm-1">
                      <button type="button" class="btn btn-light" @click.prevent="removeAdGroup(index, indexAdGroup)" v-if="indexAdGroup > 0"><i class="fa fa-minus"></i></button>
                    </div>
                  </div>
                </fieldset>
                <div class="form-group row">
                  <div class="col">
                    <button type="button" class="btn btn-primary" @click="addAdGroup(index)">ADD</button>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-group row" v-if="ruleCampaign.provider_id != 1">
              <label for="rule_campaign_bid" class="col-sm-2 control-label">Bid</label>
              <div class="col-sm-10">
                <input type="text" name="rule_campaign_bid" v-model="ruleCampaign.data.bid" class="form-control" placeholder="Enter bid">
              </div>
            </div>
          </div>
          <div class="col-sm-1">
            <button type="button" class="btn btn-light" @click.prevent="removeRuleCampaign(index)" v-if="index > 0"><i class="fa fa-minus"></i></button>
          </div>
        </div>
      </fieldset>
      <div class="form-group row">
        <div class="col">
          <button type="button" class="btn btn-primary" @click="addRuleCampaign()">ADD</button>
        </div>
      </div>
    </div>
  </div>
</template>
<script>
import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    data: {
      type: Object,
      default: null
    },
    submitData: {
      type: Object,
      default: {}
    },
  },
  components: {
    Loading,
    Select2,
  },
  computed: {
  },
  mounted() {
    console.log('Component mounted.')
    this.loadCampaigns()
  },
  watch: {
  },
  data() {
    let postData = this.submitData

    if (!postData.ruleCampaigns) {
      postData.ruleCampaigns = [{id: null, data: {bid: ''}, adGroups: []}]
    }

    return {
      isLoading: false,
      fullPage: true,
      campaignSelections: null,
      postData: postData,
      ruleCampaigns: postData.ruleCampaigns
    }
  },
  created: function() {
    this.$parent.$on('submit', this.cleanSubmitData);
  },
  methods: {
    loadCampaigns() {
      this.isLoading = true
      axios.get('/campaigns/user-campaigns').then(response => {
        this.campaignSelections = response.data.campaigns
        .map(function (campaign) {
          return {
            id: campaign.id,
            text: campaign.name,
            provider_id: campaign.provider_id
          };
        })
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    addRuleCampaign() {
      this.ruleCampaigns.push({id: null, data: {bid: ''}, adGroups: []})
    },
    removeRuleCampaign(index) {
      this.ruleCampaigns.splice(index, 1);
    },
    addAdGroup(index) {
      const campaign = this.ruleCampaigns[index];
      const groups = campaign.adGroups;
      groups.push({id: null, data: {bid: ''}});
    },
    removeAdGroup(index, indexAdGroup) {
      this.ruleCampaigns[index].adGroups.splice(indexAdGroup, 1)
    },
    campaignSelected(index, campaignId) {
      for (let i = 0; i < this.campaignSelections.length; i++) {
        if (this.campaignSelections[i].id == campaignId) {
          if (this.campaignSelections[i].provider_id == 1) {
            this.ruleCampaigns[index].provider_id = 1
            this.ruleCampaigns[index].adGroups.push({id: null, data: {bid: ''}})
            this.loadAdGroups(campaignId, index)
            break
          }
        }
      }
    },
    loadAdGroups(campaignId, index) {
      this.isLoading = true
      axios.get('/campaigns/' + campaignId + '/ad-groups/selection')
        .then((response) => {
          this.ruleCampaigns[index].adGroupSelections = response.data
        })
        .catch((err) => {
          alert(err)
        }).finally(() => {
          this.isLoading = false
        });
    },
    cleanSubmitData() {
      for (let i = 0; i < this.ruleCampaigns.length; i++) {
        if (this.ruleCampaigns[i].provider_id == 1) {
          delete this.ruleCampaigns[i].data
          delete this.ruleCampaigns[i].adGroupSelections
        } else {
          delete this.ruleCampaigns[i].adGroups
        }
      }
    }
  }
}
</script>