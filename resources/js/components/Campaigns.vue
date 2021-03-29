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
              <div class="col-md-4 col-12">
                <VueCtkDateTimePicker :shortcut="getShortcut()" :customShortcuts="shortcuts" position="bottom" v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData()"></VueCtkDateTimePicker>
              </div>
              <div class="col-md-4 col-12">
                <select2 v-model="selectedProvider" :options="formatedProviders" :settings="{ templateSelection: formatState, templateResult: formatState, multiple: false, placeholder: 'Select Traffic Source' }" @change="resetAccountAndGetData()" />
              </div>
              <div class="col-md-4 col-12">
                <select class="form-control" v-model="selectedAccount" @change="resetAdvertiserAndGetData()">
                  <option value="">-</option>
                  <option v-for="account in accounts" :value="account.open_id" :key="account.open_id">{{ account.open_id }}</option>
                </select>
              </div>
              <div class="col-md-6 col-12">
                <select class="form-control" v-model="selectedAdvertiser" @change="getData()">
                  <option value="">-</option>
                  <option v-for="advertiser in advertisers" :value="advertiser.id" :key="advertiser.id">{{ advertiser.name }}</option>
                </select>
              </div>
              <div class="col-md-6 col-12">
                <select class="form-control" v-model="selectedTracker" @change="getData()">
                  <option value="">TS Report</option>
                  <option v-for="tracker in trackers" :value="tracker.slug" :key="tracker.slug">{{ tracker.label }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body">
            <data-table :data="campaigns" :columns="campaignColumns" @on-table-props-changed="reloadData" :order-by="tableProps.column" :order-dir="tableProps.dir">
              <div slot="filters" slot-scope="{ tableFilters, perPage }">
                <div class="row mb-2">
                  <div class="col-6">
                    <select class="form-control" v-model="tableProps.length" @change="getData()">
                      <option :key="page" v-for="page in perPage">{{ page }}</option>
                    </select>
                  </div>
                  <div class="col-6">
                    <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getData()">
                  </div>
                </div>
              </div>
              <tbody slot="body" slot-scope="{ data }">
                <tr v-for="campaign in data" :key="campaign.id" :class="campaign.profit < 0 ? 'table-danger' : 'table-success'">
                  <td>{{ campaign.id }}</td>
                  <td class="fit">
                    <a :href="'/campaigns/' + campaign.id">{{ campaign.name }}</a>
                    <br>
                    <small>ID: {{ campaign.campaign_id }}</small>
                    <div class="dropdown d-inline ml-2">
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
                  <td v-if="['ACTIVE', 'RUNNING'].includes(campaign.status)" class="text-center text-success">
                    <i class="fas fa-dot-circle"></i>
                  </td>
                  <td v-else-if="['PENDING_APPROVAL'].includes(campaign.status)" class="text-center text-default">
                    <i class="fas fa-dot-circle"></i>
                  </td>
                  <td v-else class="text-center text-danger">
                    <i class="fas fa-dot-circle"></i>
                  </td>
                  <td>{{ campaign.budget }}</td>
                  <td>{{ campaign.clicks || 0 }}</td>
                  <td>{{ campaign.lp_views || 0 }}</td>
                  <td>{{ campaign.lp_clicks || 0 }}</td>
                  <td>{{ campaign.total_conversions || 0 }}</td>
                  <td>{{ campaign.total_actions || 0 }}</td>
                  <td>{{ campaign.total_actions_cr || 0 }}%</td>
                  <td>{{ campaign.cr || 0 }}%</td>
                  <td>{{ campaign.total_revenue || 0 }}</td>
                  <td>{{ campaign.cost || 0 }}</td>
                  <td>{{ campaign.profit || 0 }}</td>
                  <td>{{ campaign.roi || 0 }}%</td>
                  <td>{{ campaign.cpc || 0 }}</td>
                  <td>{{ campaign.cpa || 0 }}</td>
                  <td>{{ campaign.epc || 0 }}</td>
                  <td>{{ campaign.lp_ctr || 0 }}%</td>
                  <td>{{ campaign.lp_views_cr || 0 }}%</td>
                  <td>{{ campaign.lp_clicks_cr || 0 }}%</td>
                  <td>{{ campaign.lp_cpc || 0 }}</td>
                  <td>{{ campaign.payout || 0 }}</td>
                  <td>{{ campaign.impressions || 0 }}</td>
                </tr>
              </tbody>
            </data-table>
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
import Select2 from 'v-select2-component';
import Loading from 'vue-loading-overlay';

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    authProviders: {
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
    Select2,
    Loading
  },
  computed: {
    formatedProviders() {
      return this.providers.map(provider => {
        provider.text = provider.label
        return provider
      })
    },
    tableProps() {
      const params = (new URL(document.location)).searchParams;
      return {
        page: params.get('page') || 1,
        search: params.get('search') || '',
        length: params.get('length') || 50,
        column: params.get('column') || 'clicks',
        dir: params.get('dir') || 'desc',
      }
    }
  },
  mounted() {
    this.getData(this.tableProps, true)
  },
  watch: {
    // selectedTracker() {
    //   this.getData()
    // }
  },
  data() {
    const params = (new URL(document.location)).searchParams;
    return {
      accounts: [],
      advertisers: [],
      shortcuts: [
        { key: 'today', label: 'Today', value: 'day' },
        { key: 'yesterday', label: 'Yesterday', value: '-day' },
        { key: 'thisWeek', label: 'This week', value: 'isoWeek' },
        { key: 'lastWeek', label: 'Last week', value: '-isoWeek' },
        { key: 'last7Days', label: 'Last 7 days', value: 7 },
        { key: 'last30Days', label: 'Last 30 days', value: 30 },
        { key: 'thisMonth', label: 'This month', value: 'month' },
        { key: 'lastMonth', label: 'Last month', value: '-month' },
        { key: 'thisYear', label: 'This year', value: 'year' },
        { key: 'lastYear', label: 'Last year', value: '-year' }
      ],
      campaigns: {},
      campaignColumns: [{
        label: 'ID',
        name: 'id',
        orderable: true,
      }, {
        label: 'Name',
        name: 'name',
        orderable: true,
      }, {
        label: 'Status',
        name: 'status',
        orderable: true,
      }, {
        label: 'Budget',
        name: 'budget',
        orderable: true,
      }, {
        label: 'Clicks',
        name: 'clicks',
        orderable: true,
      }, {
        label: 'LP View',
        name: 'lp_views',
        orderable: true,
      }, {
        label: 'LP Clicks',
        name: 'lp_clicks',
        orderable: true,
      }, {
        label: 'Conv.',
        name: 'total_conversions',
        orderable: true,
      }, {
        label: 'Actions',
        name: 'total_actions',
        orderable: true,
      }, {
        label: 'Actions CR',
        name: 'total_actions_cr',
        orderable: true,
      }, {
        label: 'CR',
        name: 'cr',
        orderable: true,
      }, {
        label: 'Revenue',
        name: 'total_revenue',
        orderable: true,
      }, {
        label: 'Cost',
        name: 'cost',
        orderable: true,
      }, {
        label: 'Profit',
        name: 'profit',
        orderable: true,
      }, {
        label: 'ROI',
        name: 'roi',
        orderable: true,
      }, {
        label: 'CPC',
        name: 'cpc',
        orderable: true,
      }, {
        label: 'CPA',
        name: 'cpa',
        orderable: true,
      }, {
        label: 'EPC',
        name: 'epc',
        orderable: true,
      }, {
        label: 'LP CTR',
        name: 'lp_ctr',
        orderable: true,
      }, {
        label: 'LP Views CR',
        name: 'lp_views_cr',
        orderable: true,
      }, {
        label: 'LP Clicks CR',
        name: 'lp_clicks_cr',
        orderable: true,
      }, {
        label: 'LP CPC',
        name: 'lp_cpc',
        orderable: true,
      }, {
        label: 'Payout',
        name: 'payout',
        orderable: true,
      }, {
        label: 'Native Impression',
        name: 'impressions',
        orderable: true,
      }],
      summaryData: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      },
      selectedProvider: params.get('provider') || '',
      selectedAccount: params.get('account') || '',
      selectedAdvertiser: params.get('advertiser') || '',
      selectedTracker: params.get('provider') ? params.get('tracker') : 'redtrack',
      targetDate: {
        start: params.get('start') || this.$moment().format('YYYY-MM-DD'),
        end: params.get('end') || this.$moment().format('YYYY-MM-DD')
      },
      isLoading: false,
      showQuickActions: '',
      fullPage: true
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
    getShortcut() {
      const params = (new URL(document.location)).searchParams;
      const selectedShortcut = this.shortcuts.find(shortcut => shortcut.value === params.get('shortcut'));
      if (selectedShortcut) {
        return selectedShortcut.key
      } else {
        return 'today'
      }
    },
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
    resetAccountAndGetData(options = this.tableProps, state = false) {
      this.selectedAccount = '';
      this.selectedAdvertiser = '';
      options['page'] = 1;
      this.getData(options, state);
    },
    resetAdvertiserAndGetData(options = this.tableProps, state = false) {
      this.selectedAdvertiser = '';
      this.getData(options, state);
    },
    getData(options = this.tableProps, state = false) {
      if (!this.selectedTracker && !this.selectedProvider) {
        alert('You need to filter provider along with the tracker or use Redtrack by default!');
        return;
      }
      if (this.selectedProvider == '' && this.authProviders.length === 1) {
        this.selectedProvider = this.authProviders[0].provider.id
      }
      this.isLoading = true;
      const filters = { tracker: this.selectedTracker, provider: this.selectedProvider, account: this.selectedAccount, advertiser: this.selectedAdvertiser };
      const params = {...this.targetDate, ...filters, ...options };
      axios.post('/campaigns/filters', params)
        .then((response) => {
          this.accounts = response.data.accounts;
          this.advertisers = response.data.advertisers;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
          if (_.find(this.accounts, (account) => account.open_id === this.selectedAccount) === undefined) {
            this.selectedAccount = '';
          }
          if (_.find(this.advertisers, (advertiser) => advertiser.id == this.selectedAdvertiser) === undefined) {
            this.selectedAdvertiser = '';
          }
          const filters = { tracker: this.selectedTracker, provider: this.selectedProvider, account: this.selectedAccount, advertiser: this.selectedAdvertiser };
          const params = {...this.targetDate, ...filters, ...options };
          this.isLoading = true;
          axios.post('/campaigns/search', params)
            .then((response) => {
              this.summaryData = response.data.summary_data;
            })
            .catch((err) => {
              alert(err);
            }).finally(() => {
              this.isLoading = false;
            })
          this.isLoading = true;
          axios.post('/campaigns/data', params)
            .then((response) => {
              this.campaigns = response.data;
            })
            .catch((err) => {
              alert(err);
            }).finally(() => {
              if (!state) {
                window.history.pushState({}, null, '/campaigns?' + Object.keys(params).map(function(k) {
                  return encodeURIComponent(k) + '=' + encodeURIComponent(params[k])
                }).join('&'))
              }
              this.isLoading = false;
            })
        })
    },
    reloadData(tableProps) {
      this.getData(tableProps);
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
.table td,
.table th {
  white-space: nowrap;
  width: 1%;
  vertical-align: middle;
  padding: 0.25rem 0.75rem;
}

.table td,
.table th,
.table tr {
  border: 1px solid #c2c7cb !important;
}
</style>
