<template>
  <div class="row">
    <div class="col">
      <div class="vld-parent">
        <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
      </div>
      <div class="form-group row">
        <label for="" class="col-sm-2 control-label">Campaigns</label>
        <div class="col-sm-10">
          <select2 name="campaigns" v-model="postData.ruleCampaigns" :options="campaignSelections" :settings="{ templateSelection: formatState, templateResult: formatState, multiple: true, placeholder: 'Select Campaigns' }" />
        </div>
      </div>
    </div>
  </div>
</template>

<script>
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
    ruleActionValidate: {
      type: Boolean,
      default: false
    }
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
    return {
      isLoading: false,
      fullPage: true,
      campaignSelections: null,
      postData: this.submitData
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
    }
  }
}
</script>
