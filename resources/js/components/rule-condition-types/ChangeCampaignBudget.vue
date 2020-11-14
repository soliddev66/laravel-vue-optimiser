<template>
  <div class="form-group row">
    <label for="" class="col-sm-2 control-label">Campaign</label>
    <div class="col-sm-10">
      <select2 name="campaigns" v-model="ruleCampaignData" :options="campaignSelections" />
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

export default {
  props: {
    data: {
      type: Object,
      default: {}
    }
  },
  components: {
    Select2,
  },
  computed: {
  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {
  },
  data() {
    return {
      campaignSelections: this.data.campaigns.map(campaign => {
        return {
          id: campaign.id,
          text: campaign.name
        }
      }),
      ruleCampaignData: this.data.ruleCampaignData
    }
  },
  methods: {
  }
}
</script>