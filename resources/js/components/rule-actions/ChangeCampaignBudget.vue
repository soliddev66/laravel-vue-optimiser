<template>
  <div class="form-group row">
    <label for="" class="col-sm-2 control-label">Campaign</label>
    <div class="col-sm-10">
      <select2 name="campaigns" v-model="postData.ruleCampaignData" :options="campaignSelections" />
    </div>
    <!-- <div class="col-sm-3">
      <div class="btn-group mr-3" role="group">
        <div class="dropdown">
          <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-filter"></i> Add campaigns
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" href="#">Action</a>
            <a class="dropdown-item" href="#">Another action</a>
            <a class="dropdown-item" href="#">Something else here</a>
          </div>
        </div>
      </div>
    </div> -->
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
      default: {}
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
    return {
      isLoading: false,
      fullPage: true,
      campaignSelections: null,
      postData: this.submitData
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
    }
  }
}
</script>