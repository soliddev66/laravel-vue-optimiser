<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <table id="campaignsTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Actions</th>
                  <th>Camp. ID</th>
                  <th>Name</th>
                  <th>Status</th>
                  <th>Budget</th>
                  <th>Avg. CPC</th>
                  <th>Payout</th>
                  <th>Cost</th>
                  <th>Live Spent</th>
                  <th>TR Conv.</th>
                  <th>TS Clicks</th>
                  <th>TRK Clicks</th>
                  <th>LP Clicks</th>
                  <th>TS NET</th>
                  <th>TS ROI</th>
                  <th>eCPM</th>
                  <th>LP CR</th>
                  <th>LP CPC</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="campaign in campaigns">
                  <td>{{ campaign.id }}</td>
                  <td>
                    <a>Show</a>
                    <a :href="'/campaigns/update/' + campaign.id">Update</a>
                    <a :href="'/campaigns/delete/' + campaign.id">Delete</a>
                  </td>
                  <td>{{ campaign.id }}</td>
                  <td>{{ campaign.campaignName }}</td>
                  <td>{{ campaign.status }}</td>
                  <td>{{ campaign.budget }}</td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                  <td></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    campaigns: {
      type: Array,
      default: []
    }
  },
  mounted() {
    console.log('Component mounted.')
    console.log(this.campaigns)
  },
  data() {
    return {
      //
    }
  },
  methods: {
    //
  }
}
</script>

<style>
</style>
