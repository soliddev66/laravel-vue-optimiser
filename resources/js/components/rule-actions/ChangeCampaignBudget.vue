<template>
  <div class="row">
    <div class="col">
      <fieldset class="mb-3 p-3 rounded border" v-for="(ruleCampaign, index) in ruleCampaigns" :key="index">
        <div class="col">
          <div class="form-group row">
            <label for="" class="col-sm-2 control-label">Campaign</label>
            <div class="col-sm-10">
              <select2 name="campaigns" v-model="ruleCampaign.id" :options="campaignSelections" />
            </div>
          </div>
          <div class="form-group row">
            <label for="" class="col-sm-2 control-label">Budget</label>
            <div class="col-sm-10">
              <input type="text" name="rule_campaign_budget" v-model="ruleCampaign.data.budget" class="form-control" placeholder="Enter budget">
            </div>
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
      postData.ruleCampaigns = [{id: null, data: {budget: ''}}]
    }

    console.log(postData)

    return {
      isLoading: false,
      fullPage: true,
      campaignSelections: null,
      postData: postData,
      ruleCampaigns: postData.ruleCampaigns
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
      this.ruleCampaigns.push({id: null, data: {budget: ''}})
    }
  }
}
</script>