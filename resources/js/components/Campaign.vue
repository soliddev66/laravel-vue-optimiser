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
                  <table id="widgetsTable" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Actions</th>
                        <th>Widget ID</th>
                        <th>Bid Modifier</th>
                        <th>Calc. CPC</th>
                        <th>Avg. CPC</th>
                        <th>Cost</th>
                        <th>TR Conv.</th>
                        <th>TR Rev.</th>
                        <th>TR NET</th>
                        <th>TR ROI</th>
                        <th>TR EPC</th>
                        <th>EPC</th>
                        <th>TR CPA</th>
                        <th>Imp.</th>
                        <th>TS Clicks</th>
                        <th>TRK Clicks</th>
                        <th>LP Clicks</th>
                        <th>LP CTR</th>
                        <th>CTR</th>
                        <th>TR CVR</th>
                        <th>eCPM</th>
                        <th>LP CR</th>
                        <th>LP CPC</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="widget in widgets" class="text-center" :key="widget.id">
                        <td>{{ widget.id }}</td>
                        <td class="px-1">-</td>
                        <td>
                          {{ widget.external_site_name }}|{{ widget.device_type }}
                        </td>
                        <td>{{ widget.bid_modifier }}</td>
                        <td>{{ widget.id }}</td>
                        <td>{{ round(widget.spend / widget.clicks) || 0 }}</td>
                        <td>{{ round(widget.spend) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(0 - widget.spend) || 0 }}</td>
                        <td>{{ round(((0 - widget.spend)/widget.spend) * 100) || 0 }}%</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.impressions) || 0 }}</td>
                        <td>{{ round(widget.clicks) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.clicks/widget.impressions * 100) || 0 }}%</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.spend/widget.impressions * 1000) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                        <td>{{ round(widget.conversions) || 0 }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </v-tab>
              <v-tab title="Contents">
                <div class="table-responsive mt-3">
                  <table id="adsTable" class="table table-bordered table-hover">
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
                        <td class="border-left-0 px-1">
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
              <v-tab title="Domains">
                <div class="table-responsive mt-3">
                  <table id="domainsTable" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
                        <th>Actions</th>
                        <th>Domain ID</th>
                        <th>Avg. CPC</th>
                        <th>Cost</th>
                        <th>TR Conv.</th>
                        <th>TR Rev.</th>
                        <th>TR NET</th>
                        <th>TR ROI</th>
                        <th>TR EPC</th>
                        <th>EPC</th>
                        <th>TR CPA</th>
                        <th>Imp.</th>
                        <th>TS Clicks</th>
                        <th>TRK Clicks</th>
                        <th>LP Clicks</th>
                        <th>LP CTR</th>
                        <th>CTR</th>
                        <th>TR CVR</th>
                        <th>eCPM</th>
                        <th>LP CR</th>
                        <th>LP CPC</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr v-for="domain in domains" class="text-center" :key="domain.id">
                        <td>{{ domain.id }}</td>
                        <td class="px-1">-</td>
                        <td>
                          {{ domain.top_domain || domain.package_name }}
                        </td>
                        <td>{{ round(domain.spend / domain.clicks) || 0 }}</td>
                        <td>{{ round(domain.spend) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(0 - domain.spend) || 0 }}</td>
                        <td>{{ round(((0 - domain.spend)/domain.spend) * 100) || 0 }}%</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.impressions) || 0 }}</td>
                        <td>{{ round(domain.clicks) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.clicks/domain.impressions * 100) || 0 }}%</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.spend/domain.impressions * 1000) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                        <td>{{ round(domain.conversions) || 0 }}</td>
                      </tr>
                    </tbody>
                  </table>
                </div>
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
    this.getWidgetData()
    this.getDomainData()
  },
  data() {
    return {
      data: [],
      widgets: [],
      domains: [],
      adData: [],
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    round(value) {
      if (value !== undefined) {
        return _.round(value, 2)
      }
      return 0
    },
    adsIn(adGroup) {
      return this.adData.filter((ad) => {
        return ad.adGroupId === adGroup.id
      })
    },
    getWidgetData() {
      axios.get(`/campaigns/${this.campaign.id}/widgets`)
        .then((response) => {
          this.widgets = response.data.widgets;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          $('#widgetsTable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "stateSave": true,
            "autoWidth": false,
            "responsive": true,
          });
        });
    },
    getDomainData() {
      axios.get(`/campaigns/${this.campaign.id}/domains`)
        .then((response) => {
          this.domains = response.data.domains;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          $('#domainsTable').DataTable({
            "paging": true,
            "ordering": true,
            "info": true,
            "stateSave": true,
            "autoWidth": false,
            "responsive": true,
          });
        });
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
