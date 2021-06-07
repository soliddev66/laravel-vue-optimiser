<template>
  <div class="row">
    <div class="col">
      <fieldset class="mb-3 p-3 rounded border" v-for="(ruleCampaign, index) in ruleCampaigns" :key="index">
        <div class="row">
          <div class="col-sm-11">
            <div class="form-group row">
              <label for="" class="col-sm-2 control-label">Campaign</label>
              <div class="col-sm-10">
                <select2 name="campaigns" v-model="ruleCampaign.id" :options="campaignSelections" :settings="{ placeholder: 'Select campaign' }" />
              </div>
            </div>
            <div class="form-group">
              <div class="input-group">
                <select name="budget_set_type" v-model="ruleCampaign.data.budgetSetType" class="form-control col-sm-2">
                  <option value="1">Set Budget To</option>
                  <option value="2">Increase Budget By</option>
                  <option value="3">Descrease Budget By</option>
                </select>
                <input type="text" name="rule_campaign_budget" v-model="ruleCampaign.data.budget" class="form-control" placeholder="Enter value">
                <select name="budget_unit" v-model="ruleCampaign.data.budgetUnit" class="form-control col-sm-1">
                  <option value="1">$</option>
                  <option value="2">%</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Do not allow the Budget to be lower than</span>
                </div>
                <input type="text" name="budget_min" v-model="ruleCampaign.data.budgetMin" class="form-control" placeholder="Enter amount">
                <div class="input-group-append">
                  <span class="input-group-text">$</span>
                </div>
              </div>
            </div>

            <div class="form-group">
              <div class="input-group">
                <div class="input-group-prepend">
                  <span class="input-group-text">Do not allow the Budget to be higher than</span>
                </div>
                <input type="text" name="budget_min" v-model="ruleCampaign.data.budgetMax" class="form-control" placeholder="Enter amount">
                <div class="input-group-append">
                  <span class="input-group-text">$</span>
                </div>
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
      postData.ruleCampaigns = [{id: null, data: {budget: '', budgetSetType: 1, budgetUnit: 1, budgetMin: '', budgetMax: ''}}]
    }

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
      this.ruleCampaigns.push({id: null, data: {budget: '', budgetSetType: 1, budgetUnit: 1, budgetMin: '', budgetMax: ''}})
    },
    removeRuleCampaign(index) {
      this.ruleCampaigns.splice(index, 1);
    }
  }
}
</script>