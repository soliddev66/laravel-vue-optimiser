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
              <label class="col-sm-2 control-label">Campaign</label>
              <div class="col-sm-10">
                <select2 :id="'campaign' + index" v-model="ruleCampaign.id" :options="campaignSelections" :settings="{ templateSelection: formatState, templateResult: formatState, multiple: false, placeholder: 'Select Campaign' }" @change="ruleCampaignSelected(ruleCampaign.id)" />
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 control-label">Publishers</label>
              <div class="col-sm-10">
                <select2 :id="'sections' + index" v-model="ruleCampaign.data.sections" :options="publisherSelections[ruleCampaign.id]" :settings="{ multiple: true }" />
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 control-label">CPC Adjustment</label>
              <div class="col-sm-10">
                <input type="text" v-model="ruleCampaign.data.cpcAdjustment" class="form-control" placeholder="Enter CPC Adjustment">
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
          <button type="button" class="btn btn-primary btn-sm" @click="addRuleCampaign()">ADD</button>
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
  computed: {},
  mounted() {
    this.loadCampaigns()
  },
  watch: {},
  data() {
    let postData = this.submitData

    if (!postData.ruleCampaigns) {
      postData.ruleCampaigns = [{ id: null, data: { sections: [], cpcAdjustment: '' } }]
    } else {
      this.loadPubisherSelections(postData.ruleCampaigns);
    }

    return {
      isLoading: false,
      fullPage: true,
      campaignSelections: null,
      postData: postData,
      ruleCampaigns: postData.ruleCampaigns,
      publisherSelections: []
    }
  },
  methods: {
    formatState(state) {
      if (!state.id) {
        return state.text;
      }
      var $state = $(
        '<span><img src="' + state.icon + '" width="20px" height="20px" /> ' + state.text + '</span>'
      );
      return $state;
    },

    loadCampaigns() {
      this.isLoading = true
      axios.get('/campaigns/user-campaigns').then(response => {
        this.campaignSelections = response.data.campaigns
          .map(function(campaign) {
            return {
              id: campaign.id,
              text: campaign.name,
              icon: campaign.icon
            };
          })
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },

    loadPubisherSelections(campaigns) {
      let campaignIds = []

      for (let i = 0; i < campaigns.length; i++) {
        campaignIds.push(campaigns[i].id)
      }

      this.isLoading = true
      axios.get(`/campaigns/publisher-selections?campaign_ids=${campaignIds.join(',')}`).then(response => {
        this.publisherSelections = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },

    addRuleCampaign() {
      this.ruleCampaigns.push({ id: null, data: { sections: [], cpcAdjustment: '' } })
    },

    removeRuleCampaign(index) {
      this.ruleCampaigns.splice(index, 1);
    },

    ruleCampaignSelected(campaignId) {
      if (this.publisherSelections[campaignId]) {
        return
      }
      this.isLoading = true
      axios.get(`/campaigns/${campaignId}/publisher-selections`).then(response => {
        this.publisherSelections[campaignId] = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>
