<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <vue-tabs>
              <v-tab title="Widgets">
                <div class="table-responsive mt-3">
                  <table id="adGroupsTable" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th colspan="2">Actions</th>
                        <th>Name</th>
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
                      <tr v-for="adGroup in data" class="text-center" :key="adGroup.id">
                        <td>{{ adGroup.id }}</td>
                        <td class="border-right-0 px-1">
                          <a class="btn btn-sm btn-default border" :data-status="adGroup.status" :href="`/campaigns/${campaign.id}/ad-groups/${adGroup.id}/status`" @click.prevent="updateAdGroupStatus">
                            <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': adGroup.status == 'ACTIVE' }"></i>
                          </a>
                        </td>
                        <td class="px-1">
                          <a class="btn btn-sm btn-default border" :data-status="adGroup.status" :href="`/campaigns/${campaign.id}/ad-groups/${adGroup.id}/ads/create`">
                            <i aria-hidden="true" title="Create new ad" class="fas fa-plus"></i>
                          </a>
                        </td>
                        <td>{{ adGroup.adGroupName }}</td>
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
              </v-tab>
              <v-tab title="Contents">
                <div class="table-responsive mt-3">
                  <table id="adGroupsTable" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Actions</th>
                        <th>Name</th>
                        <th>Advertiser ID</th>
                        <th>Campaign ID</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="ad in adData" class="text-center" :key="ad.id">
                        <td>{{ ad.id }}</td>
                        <td class="px-1">
                          <a class="btn btn-sm btn-default border" :data-status="ad.status" :href="`/campaigns/${campaign.id}/ad-groups/${ad.adGroupId}/ads/status/${ad.id}`" @click.prevent="updateAdStatus">
                            <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': ad.status == 'ACTIVE' }"></i>
                          </a>
                        </td>
                        <td>
                          <a class="btn btn-sm btn-success" :href="`/campaigns/${campaign.id}/ad-groups/${ad.adGroupId}/ads/${ad.id}`">{{ ad.title }}</a>
                        </td>
                        <td>{{ ad.advertiserId }}</td>
                        <td>{{ ad.campaignId }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </v-tab>
              <v-tab title="AdGroups">
                <div class="table-responsive mt-3">
                  <table id="adGroupsTable" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Actions</th>
                        <th>Name</th>
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
                      <tr v-for="adGroup in data" class="text-center" :key="adGroup.id">
                        <td>{{ adGroup.id }}</td>
                        <td class="px-1">
                          <a class="btn btn-sm btn-default border" :data-status="adGroup.status" :href="`/campaigns/${campaign.id}/ad-groups/status/${adGroup.id}`" @click.prevent="updateAdGroupStatus">
                            <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': adGroup.status == 'ACTIVE' }"></i>
                          </a>
                        </td>
                        <td>{{ adGroup.adGroupName }}</td>
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
              </v-tab>
              <v-tab title="Domains">
                Redtrack sub1
              </v-tab>
              <v-tab title="Rules">
                Rules
              </v-tab>
              <v-tab title="Performance">
                Performance
              </v-tab>
            </vue-tabs>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

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
  components: {
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    console.log(this.groups)
    this.data = this.groups
    this.adData = this.ads.filter(ad => ad.adGroupId === this.data[0].id)
  },
  data() {
    return {
      data: [],
      adData: [],
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    adsIn(adGroup) {
      return this.adData.filter((ad) => {
        return ad.adGroupId === adGroup.id
      })
    },
    getData() {
      this.isLoading = true;
      axios.post('/campaigns/' + this.campaign.id + '/ad-groups/data')
        .then((response) => {
          this.data = response.data.adGroups
          this.adData = response.data.ads.filter(ad => ad.adGroupId === this.data[0].id)
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
        });
    },
    updateAdGroupStatus(e) {
      this.isLoading = true;
      axios.post(e.target.getAttribute('href'), {
        status: e.target.dataset.status
      }).then((response) => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.getData();
        }
      }).catch((err) => {
        alert(err);
      });
    },
    updateAdStatus(e) {
      this.isLoading = true;
      axios.post(e.target.getAttribute('href'), {
        status: e.target.dataset.status
      }).then((response) => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.getData();
        }
      }).catch((err) => {
        alert(err);
      });
    }
  }
}
</script>

<style>
</style>
