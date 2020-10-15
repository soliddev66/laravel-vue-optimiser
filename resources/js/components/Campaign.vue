<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <table id="adGroupsTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Ads</th>
                  <th>Advertiser ID</th>
                  <th>Campaign ID</th>
                  <th>ECPA Goal</th>
                  <th>DPA Audience Strategy</th>
                  <th>Tracking Url</th>
                  <th>Advanced Geo Pos</th>
                  <th>Advanced Geo Neg</th>
                  <th>Bidding Strategy</th>
                  <th>Custom Parameters</th>
                  <th>Product Set ID</th>
                  <th>Editorial Status</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="adGroup in data" class="text-center">
                  <td>{{ adGroup.id }}</td>
                  <td>{{ adGroup.adGroupName }}</td>
                  <td>
                    <p v-for="ad in adsIn(adGroup)">
                      <a class="btn btn-sm btn-success" :href="`/campaigns/${campaign.id}/ad-groups/${adGroup.id}/ads/${ad.id}`">{{ ad.id }}</a>
                    </p>
                  </td>
                  <td>{{ adGroup.advertiserId }}</td>
                  <td>{{ adGroup.campaignId }}</td>
                  <td>{{ adGroup.ecpaGoal }}</td>
                  <td>{{ adGroup.dpaAudienceStrategy }}</td>
                  <td>{{ adGroup.trackingUrl }}</td>
                  <td>{{ adGroup.advancedGeoPos }}</td>
                  <td>{{ adGroup.advancedGeoNeg }}</td>
                  <td>{{ adGroup.biddingStrategy }}</td>
                  <td>{{ adGroup.customParameters }}</td>
                  <td>{{ adGroup.productSetId }}</td>
                  <td>{{ adGroup.editorialStatus }}</td>
                  <td>{{ adGroup.startDateStr }}</td>
                  <td>{{ adGroup.endDateStr }}</td>
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
import _ from 'lodash';

export default {
  props: {
    campaign: {
      type: Object,
      default: null
    },
    groups: {
      type: Array
    },
    ads: {
      type: Array
    }
  },
  mounted() {
    console.log('Component mounted.')
    console.log(this.groups)
    this.data = this.groups
  },
  data() {
    return {
      data: []
    }
  },
  methods: {
    adsIn(adGroup) {
      return this.ads.filter((ad) => {
        return ad.adGroupId === adGroup.id
      })
    }
  }
}
</script>

<style>
</style>
