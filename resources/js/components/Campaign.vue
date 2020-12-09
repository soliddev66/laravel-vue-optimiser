<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <summary-data :summaryData="summaryData"></summary-data>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-md-3 col-12">
                <VueCtkDateTimePicker id="targetDate" position="bottom" v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
              </div>
              <div class="col-md-3 col-12">
                <select id="provider" class="form-control" v-model="selectedProvider" @change="getData">
                  <option v-for="provider in providers" :value="provider.slug">{{ provider.label }}</option>
                </select>
              </div>
              <div class="col-md-3 col-12">
                <select id="account" class="form-control" v-model="selectedAccount" @change="getData">
                  <option v-for="account in accounts" :value="account.id">{{ account.open_id }}</option>
                </select>
              </div>
              <div class="col-md-3 col-12">
                <select id="tracker" class="form-control" v-model="selectedTracker" @change="getData">
                  <option value="">-</option>
                  <option v-for="tracker in trackers" :value="tracker.slug">{{ tracker.label }}</option>
                </select>
              </div>
            </div>
          </div>
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
                      <tr v-if="selectedTracker">
                        <th>ID</th>
                        <th>Actions</th>
                        <th>Domain ID</th>
                        <th>Clicks</th>
                        <th>LP Views</th>
                        <th>LP Clicks</th>
                        <th>Pre LP Clicks</th>
                        <th>LP CTR</th>
                        <th>Conversion</th>
                        <th>CR</th>
                        <th>Total Actions</th>
                        <th>TR</th>
                        <th>Conversion Revenue</th>
                        <th>Total Revenue</th>
                        <th>Cost</th>
                        <th>Profit</th>
                        <th>ROI</th>
                        <th>CPC</th>
                        <th>CPA</th>
                        <th>EPC</th>
                      </tr>
                      <tr v-else>
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
                      <tr v-for="(domain, index) in domains" class="text-center" :key="domain.id">
                        <td>{{ domain.id || index }}</td>
                        <td class="px-1">-</td>
                        <td>
                          {{ domain.sub1 || domain.top_domain || domain.package_name }}
                        </td>
                        <td v-if="selectedTracker">{{ domain.clicks || 0 }}</td>
                        <td v-else>{{ round(domain.spend / domain.clicks) || 0 }}</td>
                        <td v-if="selectedTracker">{{ count(domain.lp_views) || 0 }}</td>
                        <td v-else>{{ round(domain.spend) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.lp_clicks || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.prelp_clicks || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.lp_ctr || 0 }}%</td>
                        <td v-else>{{ round(0 - domain.spend) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.conversions || 0 }}</td>
                        <td v-else>{{ round(((0 - domain.spend)/domain.spend) * 100) || 0 }}%</td>
                        <td v-if="selectedTracker">{{ domain.cr || 0 }}%</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.total_actions || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.tr || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.conversion_revenue || 0 }}</td>
                        <td v-else>{{ round(domain.impressions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.total_revenue || 0 }}</td>
                        <td v-else>{{ round(domain.clicks) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.cost || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.profit || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.roi || 0 }}%</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.cpc || 0 }}</td>
                        <td v-else>{{ round(domain.clicks/domain.impressions * 100) || 0 }}%</td>
                        <td v-if="selectedTracker">{{ domain.cpa || 0 }}</td>
                        <td v-else>{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="selectedTracker">{{ domain.epc || 0 }}</td>
                        <td v-else>{{ round(domain.spend/domain.impressions * 1000) || 0 }}</td>
                        <td v-if="!selectedTracker">{{ round(domain.conversions) || 0 }}</td>
                        <td v-if="!selectedTracker">{{ round(domain.conversions) || 0 }}</td>
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
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import Loading from 'vue-loading-overlay'

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  props: {
    campaign: {
      type: Object,
      default: null
    },
    providers: {
      type: Array,
      default: []
    },
    accounts: {
      type: Array,
      default: []
    },
    trackers: {
      type: Array,
      default: []
    }
  },
  components: {
    VueCtkDateTimePicker,
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.getData()
  },
  data() {
    return {
      data: [],
      summaryData: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      },
      domains: [],
      adData: [],
      selectedProvider: 'yahoo',
      selectedAccount: 1,
      selectedTracker: 'redtrack',
      targetDate: {
        start: this.$moment().format('YYYY-MM-DD'),
        end: this.$moment().format('YYYY-MM-DD')
      },
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    count(array, key) {
      if (array !== undefined) {
        return _.round(_.sumBy(array, (value) => value[key]), 2)
      }
      return 0
    },
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
    getDomainData() {
      axios.get(`/campaigns/${this.campaign.id}/domains`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker } }
        })
        .then((response) => {
          this.domains = response.data.domains;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          $('#domainsTable').DataTable({
            retrieve: true,
            paging: true,
            ordering: true,
            info: true,
            stateSave: true,
            autoWidth: false,
            pageLength: 50,
          });
        });
    },
    getData() {
      this.isLoading = true;
      $('#targetDate-input').trigger('change');
      this.getDomainData();
      axios.post('/campaigns/' + this.campaign.id + '/ad-groups/data', {...this.targetDate, ... { tracker: this.selectedTracker } })
        .then((response) => {
          this.data = response.data.ad_groups
          this.adData = response.data.ads.filter(ad => ad.adGroupId === this.data[0].id)
          this.summaryData = response.data.summary_data
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
          $('#adGroupsTable, #adsTable').DataTable({
            retrieve: true,
            paging: true,
            ordering: true,
            info: true,
            stateSave: true,
            autoWidth: false,
            pageLength: 50,
          });
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
      }).finally(() => {
        this.isLoading = false;
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
      }).finally(() => {
        this.isLoading = false;
      });
    }
  }
}
</script>

<style>
</style>
