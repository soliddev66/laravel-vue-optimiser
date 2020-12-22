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
              <div class="col-md-6 col-12">
                <VueCtkDateTimePicker id="targetDate" position="bottom" v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
              </div>
              <div class="col-md-6 col-12">
                <select id="tracker" class="form-control" v-model="selectedTracker" @change="getData">
                  <option value="">-</option>
                  <option v-for="tracker in trackers" :value="tracker.slug">{{ tracker.label }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body">
            <tabs :theme="theme">
              <tab title="Widgets" hash="widgets" @click.prevent="getWidgetData()">
                <data-table :data="widgets" :columns="widgetColumns" @on-table-props-changed="reloadWidgetData"></data-table>
                <div class="table-responsive mt-3 d-none">
                  <table id="widgetsTable" class="table table-bordered table-hover">
                    <thead>
                      <tr>
                        <th>ID</th>
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
              </tab>
              <tab title="Contents" hash="contents" @click.prevent="getContentData()">
                <data-table :data="contents" :columns="contentColumns" @on-table-props-changed="reloadContentData"></data-table>
              </tab>
              <tab title="AdGroups" hash="ad-groups" @click.prevent="getAdGroupData()">
                <data-table :data="adGroups" :columns="adGroupColumns" @on-table-props-changed="reloadAdGroupData"></data-table>
              </tab>
              <tab title="Domains" hash="domains" @click.prevent="getDomainData()">
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
              </tab>
              <tab title="Rules" hash="rules">
                Rules
              </tab>
              <tab title="Performance" hash="performance">
                Performance
              </tab>
            </tabs>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import { Tabs, Tab } from '@hiendv/vue-tabs';
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import Loading from 'vue-loading-overlay';

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  props: {
    campaign: {
      type: Object,
      default: null
    },
    trackers: {
      type: Array,
      default: []
    }
  },
  components: {
    Tabs,
    Tab,
    VueCtkDateTimePicker,
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.getData();
  },
  data() {
    let params = (new URL(document.location)).searchParams;
    return {
      widgets: {},
      contents: {},
      adGroups: {},
      domains: {},
      widgetColumns: [
        { label: 'ID', name: 'id', orderable: true },
        { label: 'Widget ID', name: 'widget_id', orderable: true },
        { label: 'Bid Modifier', name: 'bid_modifier', orderable: true },
        { label: 'Calc. CPC', name: 'calc_cpc', orderable: true },
        { label: 'Avg. CPC', name: 'average_cpc', orderable: true },
        { label: 'Spend', name: 'spend', orderable: true },
        { label: 'TR Conv.', name: 'tr_conv', orderable: true },
        { label: 'TR Rev.', name: 'tr_rev', orderable: true },
        { label: 'TR NET', name: 'tr_net', orderable: true },
        { label: 'TR ROI', name: 'tr_roi', orderable: true },
        { label: 'TR EPC', name: 'tr_epc', orderable: true },
        { label: 'EPC', name: 'epc', orderable: true },
        { label: 'TR CPA', name: 'tr_cpa', orderable: true },
        { label: 'Impressions', name: 'impressions', orderable: true },
        { label: 'TS Clicks', name: 'ts_clicks', orderable: true },
        { label: 'TRK Clicks', name: 'trk_clicks', orderable: true },
        { label: 'LP Clicks', name: 'lp_clicks', orderable: true },
        { label: 'LP CTR', name: 'lp_ctr', orderable: true },
        { label: 'CTR', name: 'ctr', orderable: true },
        { label: 'TR CVR', name: 'tr_cvr', orderable: true },
        { label: 'eCPM', name: 'ecpm', orderable: true },
        { label: 'LP CR', name: 'lp_cr', orderable: true },
        { label: 'LP CPC', name: 'lp_cpc', orderable: true }
      ],
      contentColumns: [
        { label: 'ID', name: 'id', orderable: true },
        { label: 'Ad. ID', name: 'ad_id', orderable: true },
        { label: 'Name', name: 'name', orderable: true },
        { label: 'Status', name: 'status', orderable: true },
        { label: 'Payout', name: 'payout', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'LP View', name: 'lp_views', orderable: true },
        { label: 'LP Clicks', name: 'lp_clicks', orderable: true },
        { label: 'Conversion', name: 'conversions', orderable: true },
        { label: 'Total Actions', name: 'total_actions', orderable: true },
        { label: 'Total Actions CR', name: 'total_actions_cr', orderable: true },
        { label: 'CR', name: 'cr', orderable: true },
        { label: 'Total Revenue', name: 'total_revenue', orderable: true },
        { label: 'Cost', name: 'cost', orderable: true },
        { label: 'Profit', name: 'profit', orderable: true },
        { label: 'ROI', name: 'roi', orderable: true },
        { label: 'CPC', name: 'cpc', orderable: true },
        { label: 'CPA', name: 'cpa', orderable: true },
        { label: 'EPC', name: 'epc', orderable: true },
        { label: 'LP CTR', name: 'lp_ctr', orderable: true },
        { label: 'LP Views CR', name: 'lp_views_cr', orderable: true },
        { label: 'LP Clicks CR', name: 'lp_clicks_cr', orderable: true },
        { label: 'LP CPC', name: 'lp_cpc', orderable: true }
      ],
      adGroupColumns: [
        { label: 'ID', name: 'id', orderable: true },
        { label: 'Camp. ID', name: 'campaign_id', orderable: true },
        { label: 'Ad Group ID', name: 'ad_group_id', orderable: true },
        { label: 'Name', name: 'name', orderable: true },
        { label: 'Status', name: 'status', orderable: true }
      ],
      summaryData: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      },
      adData: [],
      selectedTracker: 'redtrack',
      targetDate: {
        start: this.$moment().subtract(30, 'days').format('YYYY-MM-DD'),
        end: this.$moment().format('YYYY-MM-DD')
      },
      isLoading: false,
      fullPage: true,
      tableProps: {
        page: params.get('page') || 1,
        search: params.get('search') || '',
        length: params.get('length') || 10,
        column: params.get('column') || 'id',
        dir: params.get('dir') || 'asc',
      },
      theme: {
        tabs: 'custom-tabs',
        items: 'custom-items',
        item: 'custom-item',
        'item--active': 'custom-item-active',
        'item--end': 'custom-item-end',
        panel: 'custom-panel'
      }
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
    getData() {
      switch (location.hash) {
        case '#contents':
          this.getContentData();
          break;
        case '#ad-groups':
          this.getAdGroupData();
          break;
        case '#domains':
          this.getDomainData();
          break;
        case '#rules':
          break;
        case '#performance':
          break;
        default:
          this.getWidgetData();
          break;
      }
    },
    reloadWidgetData(tableProps) {
      this.getWidgetData(tableProps);
    },
    reloadContentData(tableProps) {
      this.getContentData(tableProps);
    },
    reloadAdGroupData(tableProps) {
      this.getAdGroupData(tableProps);
    },
    getWidgetData(options = this.tableProps) {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/widgets`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.widgets = response.data;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
        });
    },
    getDomainData(options = this.tableProps) {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/domains`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.domains = response.data.domains;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
        });
    },
    getAdGroupData(options = this.tableProps) {
      this.isLoading = true;
      axios.get('/campaigns/' + this.campaign.id + '/ad-groups', {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.adGroups = response.data
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
        });
    },
    getContentData(options = this.tableProps) {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/contents`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.contents = response.data;
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
          this.getAdGroupData();
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
          this.getContentData();
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
.table td,
.table th {
  white-space: nowrap;
  width: 1%;
}

.custom-tabs {}

.custom-items {}

.custom-item {
  display: inline-block;
  padding: .5rem;
  text-decoration: none;
  color: #07a;
}

.custom-item-active {
  color: #905;
}

.custom-item-end {}

.custom-panel {
  padding: 1rem;
  border: 1px dashed #cdcdcd;
}
</style>
