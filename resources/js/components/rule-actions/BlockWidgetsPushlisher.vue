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
                <select2 name="campaigns" v-model="ruleCampaign.id" :options="campaignSelections" @change="ruleCampaignSelected(ruleCampaign.id)" />
              </div>
            </div>
            <div class="form-group row">
              <label for="" class="col-sm-2 control-label">Widgets</label>
              <div class="col-sm-10">
                <select2 name="widgets" v-model="ruleCampaign.data.widgets" :options="widgetSelections[ruleCampaign.id]" :settings="{ multiple: true }" />
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
  computed: {
  },
  mounted() {
    this.loadCampaigns()
  },
  watch: {
  },
  data() {
    let postData = this.submitData

    if (!postData.ruleCampaigns) {
      postData.ruleCampaigns = [{id: null, data: {widgets: []}}]
    }

    return {
      isLoading: false,
      fullPage: true,
      campaignSelections: null,
      postData: postData,
      ruleCampaigns: postData.ruleCampaigns,
      widgetSelections: []
    }
  },
  methods: {
    loadCampaigns() {
      this.isLoading = true
      axios.get('/campaigns/user-campaigns').then(response => {
        this.campaignSelections = response.data.campaigns
        .map(function (campaign) {
          return {
            id: campaign.id,
            text: campaign.name
          };
        })
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    addRuleCampaign() {
      this.ruleCampaigns.push({id: null, data: {widgets: []}})
    },
    removeRuleCampaign(index) {
      this.ruleCampaigns.splice(index, 1);
    },
    ruleCampaignSelected(campaignId) {
      if (this.widgetSelections[campaignId]) {
        return
      }
      this.isLoading = true
      axios.get(`/campaigns/${campaignId}/targets?status=active`).then(response => {
        this.widgetSelections[campaignId] = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>