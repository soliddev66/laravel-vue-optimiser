<template>
  <div class="container-fluid">
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
                <VueCtkDateTimePicker position="bottom" v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
              </div>
              <div class="col-md-3 col-12">
                <select class="form-control" v-model="selectedProvider" @change="getData">
                  <option value="">-</option>
                  <option v-for="provider in providers" :value="provider.id">{{ provider.label }}</option>
                </select>
              </div>
              <div class="col-md-3 col-12">
                <select class="form-control" v-model="selectedAccount" @change="getData">
                  <option value="">-</option>
                  <option v-for="account in accounts" :value="account.open_id">{{ account.open_id }}</option>
                </select>
              </div>
              <div class="col-md-3 col-12">
                <select class="form-control" v-model="selectedTracker" @change="getData">
                  <option value="">-</option>
                  <option v-for="tracker in trackers" :value="tracker.slug">{{ tracker.label }}</option>
                </select>
              </div>
            </div>
            <div class="row mt-3">
              <div class="col-md-6 col-12">
                <pagination :data="campaigns" @pagination-change-page="getData" class="mb-0"></pagination>
              </div>
              <div class="col-md-6 col-12">
                <input type="text" class="form-control" placeholder="Search campaign by name..." v-model="query" v-debounce:1s="getData">
              </div>
            </div>
          </div>
          <div class="card-body table-responsive">
            <table ref="campaignsTable" id="campaignsTable" class="table table-bordered table-hover text-center">
              <thead class="border">
                <tr>
                  <th>ID</th>
                  <th>Traffic Source</th>
                  <th>Actions</th>
                  <th class="fit">Camp. ID</th>
                  <th class="fit">Name</th>
                  <th>Status</th>
                  <th>Budget</th>
                  <th>Payout</th>
                  <th>Clicks</th>
                  <th>LP View</th>
                  <th>LP Clicks</th>
                  <th>Conversion</th>
                  <th>Total Actions</th>
                  <th>Total Actions CR</th>
                  <th>CR</th>
                  <th>Total Revenue</th>
                  <th>Cost</th>
                  <th>Profit</th>
                  <th>ROI</th>
                  <th>CPC</th>
                  <th>CPA</th>
                  <th>EPC</th>
                  <th>LP CTR</th>
                  <th>LP Views CR</th>
                  <th>LP Clicks CR</th>
                  <th>LP CPC</th>
                </tr>
              </thead>
              <tbody v-if="campaigns.data && campaigns.data.length">
                <tr v-for="campaign in campaigns.data" :key="campaign.id">
                  <td>{{ campaign.id }}</td>
                  <td class="text-capitalize">{{ providerName(campaign) }}</td>
                  <td class="border-right-0 px-1">
                    <div class="dropdown">
                      <button class="btn btn-sm border" type="button" @click="showQuickAct(campaign.id)">
                        <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                      </button>
                      <div class="dropdown-menu px-2" :class="{ 'show': showQuickActions === campaign.id }">
                        <a class="btn btn-sm btn-default border" :href="'/campaigns/status/' + campaign.id" @click.prevent="updateCampaignStatus">
                          <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': campaign.status == 'ACTIVE' }"></i>
                        </a>
                        <a class="btn btn-sm btn-default border" :href="'/campaigns/edit/' + campaign.id"><i class="fas fa-edit"></i></a>
                        <a class="btn btn-sm btn-default border" :href="'/campaigns/create/' + campaign.id"><i class="fas fa-clone"></i></a>
                        <a class="btn btn-sm btn-default border" :href="'/campaigns/delete/' + campaign.id" @click.prevent="deleteCampaign"><i class="fas fa-trash"></i></a>
                      </div>
                    </div>
                  </td>
                  <td class="fit">{{ campaign.campaign_id }}</td>
                  <td class="fit"><a :href="'/campaigns/' + campaign.id">{{ campaign.name }}</a></td>
                  <td v-if="campaign.status === 'ACTIVE'" class="text-success">
                    {{ campaign.status }}
                  </td>
                  <td v-else class="text-danger">
                    {{ campaign.status }}
                  </td>
                  <td>{{ campaign.budget }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'revenue') / count(campaign.redtrack_report, 'conversions')) || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'clicks') || count(campaign.performance_stats, 'clicks') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'lp_views') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'lp_clicks') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'conversions') || count(campaign.performance_stats, 'conversions') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'total_conversions') || count(campaign.performance_stats, 'total_conversions') || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'conversions') / count(campaign.redtrack_report, 'clicks') * 100) || round(count(campaign.performance_stats, 'conversions') / count(campaign.performance_stats, 'clicks') * 100) || 0 }}%</td>
                  <td>{{ round(count(campaign.redtrack_report, 'total_conversions') / count(campaign.redtrack_report, 'clicks') * 100) || round(count(campaign.performance_stats, 'total_conversions') / count(campaign.performance_stats, 'clicks') * 100) || 0 }}%</td>
                  <td>{{ count(campaign.redtrack_report, 'total_revenue') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'cost') || count(campaign.performance_stats, 'spend') || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'total_revenue') - count(campaign.redtrack_report, 'cost')) || round(0 - count(campaign.performance_stats, 'spend')) || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'profit') / count(campaign.redtrack_report, 'cost') * 100) || round((0 - count(campaign.performance_stats, 'spend')) / count(campaign.performance_stats, 'spend') * 100) || 0 }}%</td>
                  <td>{{ round(count(campaign.redtrack_report, 'cost') / count(campaign.redtrack_report, 'clicks')) || round(count(campaign.performance_stats, 'spend') / count(campaign.performance_stats, 'clicks')) || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'cost') / count(campaign.redtrack_report, 'total_conversions')) || round(count(campaign.performance_stats, 'spend') / count(campaign.performance_stats, 'total_conversions')) || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'total_revenue') / count(campaign.redtrack_report, 'clicks')) || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'lp_clicks') / count(campaign.redtrack_report, 'lp_views') * 100) || 0 }}%</td>
                  <td>{{ round(count(campaign.redtrack_report, 'total_conversions') / count(campaign.redtrack_report, 'lp_views') * 100) || 0 }}%</td>
                  <td>{{ round(count(campaign.redtrack_report, 'total_conversions') / count(campaign.redtrack_report, 'lp_clicks') * 100) || 0 }}%</td>
                  <td>{{ round(count(campaign.redtrack_report, 'cost') / count(campaign.redtrack_report, 'lp_clicks')) || 0 }}</td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr>
                  <td colspan="26">No campaign found.</td>
                </tr>
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <div class="row justify-content-center">
              <div class="col-12 col-md-12">
                <button class="btn btn-default border" @click.prevent="exportCsv">Download CSV</button>
                <button class="btn btn-default border" @click.prevent="exportExcel">Download Excel</button>
              </div>
            </div>
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
    providers: {
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
  watch: {
    selectedTracker() {
      this.getData()
    }
  },
  data() {
    return {
      accounts: [],
      campaigns: {},
      summaryData: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      },
      selectedProvider: '',
      selectedAccount: '',
      selectedTracker: 'redtrack',
      targetDate: {
        start: this.$moment().subtract(30, 'days').format('YYYY-MM-DD'),
        end: this.$moment().format('YYYY-MM-DD')
      },
      query: '',
      isLoading: false,
      showQuickActions: '',
      fullPage: true
    }
  },
  methods: {
    showQuickAct(campaignId) {
      if (this.showQuickActions === campaignId) {
        this.showQuickActions = ''
      } else {
        this.showQuickActions = campaignId
      }
    },
    avg(array, key) {
      if (array !== undefined) {
        return _.round(_.meanBy(array, (value) => value[key]), 2)
      }
      return 0
    },
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
    providerName(campaign) {
      return this.providers.find(provider => provider.id === campaign.provider_id) ? this.providers.find(provider => provider.id === campaign.provider_id).label : 'N/A'
    },
    getData(page = 1) {
      const params = {...this.targetDate, ... { tracker: this.selectedTracker, provider: this.selectedProvider, account: this.selectedAccount, query: this.query, page: page } };
      axios.post('/campaigns/search', params)
        .then((response) => {
          this.accounts = response.data.accounts;
          this.campaigns = response.data.campaigns;
          this.summaryData = response.data.summary_data;
        })
        .catch((err) => {
          alert(err);
        });
    },
    exportExcel() {
      location.href = '/campaigns/export-excel?start=' + this.targetDate.start.split('T')[0] + '&end=' + this.targetDate.end.split('T')[0]
    },
    exportCsv() {
      location.href = '/campaigns/export-csv?start=' + this.targetDate.start.split('T')[0] + '&end=' + this.targetDate.end.split('T')[0]
    },
    updateCampaignStatus(e) {
      this.isLoading = true;
      axios.post(e.target.getAttribute('href'))
        .then((response) => {
          if (response.data.errors) {
            alert(response.data.errors[0])
          } else {
            this.getData();
          }
        })
        .catch((err) => {
          alert(err);
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    deleteCampaign(e) {
      if (confirm('Are you sure to delete this campaign?')) {
        this.isLoading = true;
        axios.post(e.target.getAttribute('href'))
          .then((response) => {
            if (response.data.errors) {
              alert(response.data.errors[0])
            } else {
              this.getData();
              alert('Delete the campaign successfully!');
            }
          })
          .catch((err) => {
            alert(err);
          })
          .finally(() => {
            this.isLoading = false;
          });
      }
    }
  }
}
</script>

<style>
.table td.fit,
.table th.fit {
  white-space: nowrap;
  width: 1%;
}
</style>
