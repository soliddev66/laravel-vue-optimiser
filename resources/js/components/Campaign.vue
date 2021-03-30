<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="true"></loading>
    </div>
    <summary-data :summaryData="summaryData"></summary-data>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-md-6 col-12">
                <VueCtkDateTimePicker :shortcuts="getShortcut()" :customShortcuts="shortcuts" id="targetDate" position="bottom" v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
              </div>
              <div class="col-md-6 col-12">
                <select id="tracker" class="form-control" v-model="selectedTracker" @change="getData">
                  <option value="">-</option>
                  <option v-for="tracker in trackers" :value="tracker.slug" :key="tracker.slug">{{ tracker.label }}</option>
                </select>
              </div>
            </div>
          </div>
          <div class="card-body">
            <ul class="nav nav-pills mb-3" role="tablist">
              <li class="nav-item" v-if="[1].includes(campaign.provider_id)">
                <a class="nav-link" :class="{ 'active': show === 0 }" id="widgets-tab" data-toggle="pill" href="#widgets" role="tab" aria-controls="widgets" aria-selected="true" @click.prevent="getWidgetData()">Widgets</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 1 }" id="contents-tab" data-toggle="pill" href="#contents" role="tab" aria-controls="contents" aria-selected="false" @click.prevent="getContentData()">Contents</a>
              </li>
              <li class="nav-item" v-if="[1,3,5].includes(campaign.provider_id)">
                <a class="nav-link" :class="{ 'active': show === 2 }" id="ad-groups-tab" data-toggle="pill" href="#ad-groups" role="tab" aria-controls="ad-groups" aria-selected="false" @click.prevent="getAdGroupData()">Ad Groups</a>
              </li>
              <li class="nav-item" v-if="[2].includes(campaign.provider_id)">
                <a class="nav-link" :class="{ 'active': show === 2 }" id="publishers-tab" data-toggle="pill" href="#publishers" role="tab" aria-controls="publishers" aria-selected="false" @click.prevent="getPublisherData()">Publishers</a>
              </li>
              <li class="nav-item" v-if="[1,4].includes(campaign.provider_id)">
                <a class="nav-link" :class="{ 'active': show === 3 }" id="domains-tab" data-toggle="pill" href="#domains" role="tab" aria-controls="domains" aria-selected="false" @click.prevent="getDomainData()">Domains</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 4 }" id="rules-tab" data-toggle="pill" href="#rules" role="tab" aria-controls="rules" aria-selected="false" @click.prevent="getRuleData()">Rules</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 5 }" id="performance-tab" data-toggle="pill" href="#performance" role="tab" aria-controls="performance" aria-selected="false" @click.prevent="getPerformanceData()">Performance</a>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade" :class="{ 'show active': show === 0 }" id="widgets" role="tabpanel" aria-labelledby="widgets-tab">
                <data-table :data="widgets" :columns="widgetColumns" @on-table-props-changed="reloadWidgetData" :order-by="tableProps.column" :order-dir="tableProps.dir">
                  <div slot="filters" slot-scope="{ tableFilters, perPage }">
                    <div class="row mb-2">
                      <div class="col-6">
                        <select class="form-control" v-model="tableProps.length" @change="getWidgetData()">
                          <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getWidgetData()">
                      </div>
                    </div>
                  </div>
                </data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 1 }" id="contents" role="tabpanel" aria-labelledby="contents-tab">
                <a class="btn btn-primary mb-2 float-right" v-if="campaign.provider_id == 2 || campaign.provider_id == 4" :href="`/campaigns/${campaign.id}/ad-groups/ad-group/ads/create`">Create New Ad</a>
                <data-table :data="contents" :columns="contentColumns" @on-table-props-changed="reloadContentData" :order-by="tableProps.column" :order-dir="tableProps.dir">
                  <div slot="filters" slot-scope="{ tableFilters, perPage }">
                    <div class="row mb-2">
                      <div class="col-6">
                        <select class="form-control" v-model="tableProps.length" @change="getContentData()">
                          <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getContentData()">
                      </div>
                    </div>
                  </div>
                </data-table>
              </div>
              <div class="tab-pane fade" v-if="[1,3,4,5].includes(campaign.provider_id)" :class="{ 'show active': show === 2 }" id="ad-groups" role="tabpanel" aria-labelledby="ad-groups-tab">
                <data-table :data="adGroups" :columns="adGroupColumns" @on-table-props-changed="reloadAdGroupData" :order-by="tableProps.column" :order-dir="tableProps.dir">
                  <div slot="filters" slot-scope="{ tableFilters, perPage }">
                    <div class="row mb-2">
                      <div class="col-6">
                        <select class="form-control" v-model="tableProps.length" @change="getAdGroupData()">
                          <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getAdGroupData()">
                      </div>
                    </div>
                  </div>
                </data-table>
              </div>
              <div class="tab-pane fade" v-if="[2].includes(campaign.provider_id)" :class="{ 'show active': show === 2 }" id="publishers" role="tabpanel" aria-labelledby="publishers-tab">
                <data-table :data="publishers" :columns="publisherColumns" @on-table-props-changed="reloadPublisherData" :order-by="tableProps.column" :order-dir="tableProps.dir">
                  <div slot="filters" slot-scope="{ tableFilters, perPage }">
                    <div class="row mb-2">
                      <div class="col-6">
                        <select class="form-control" v-model="tableProps.length" @change="getPublisherData()">
                          <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getPublisherData()">
                      </div>
                    </div>
                  </div>
                </data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 3 }" id="domains" role="tabpanel" aria-labelledby="domains-tab">
                <data-table :data="domains" :columns="domainColumns" @on-table-props-changed="reloadDomainData" :order-by="tableProps.column" :order-dir="tableProps.dir">
                  <div slot="filters" slot-scope="{ tableFilters, perPage }">
                    <div class="row mb-2">
                      <div class="col-6">
                        <select class="form-control" v-model="tableProps.length" @change="getDomainData()">
                          <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getDomainData()">
                      </div>
                    </div>
                  </div>
                </data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 4 }" id="rules" role="tabpanel" aria-labelledby="rules-tab">
                <data-table :data="rules" :columns="ruleColumns" @on-table-props-changed="reloadRuleData" :order-by="tableProps.column" :order-dir="tableProps.dir">
                  <div slot="filters" slot-scope="{ tableFilters, perPage }">
                    <div class="row mb-2">
                      <div class="col-6">
                        <select class="form-control" v-model="tableProps.length" @change="getRuleData()">
                          <option :key="page" v-for="page in perPage">{{ page }}</option>
                        </select>
                      </div>
                      <div class="col-6">
                        <input name="search" class="form-control" v-model="tableProps.search" placeholder="Search Table" @blur="getRuleData()">
                      </div>
                    </div>
                  </div>
                </data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 5 }" id="performance" role="tabpanel" aria-labelledby="performance-tab">
                <bar-chart v-if="performance" :chart-data="performance"></bar-chart>
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
import Loading from 'vue-loading-overlay';
import ActionsComponent from './includes/ActionsComponent.vue';
import NameComponent from './includes/NameComponent.vue';
import ImageComponent from './includes/ImageComponent.vue';
import BarChart from '../plugins/BarChart.js';

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
    VueCtkDateTimePicker,
    BarChart,
    Loading
  },
  mounted() {
    this.getData();
  },
  computed: {
    tableProps() {
      let params = (new URL(document.location)).searchParams;
      return {
        page: params.get('page') || 1,
        search: params.get('search') || '',
        length: params.get('length') || 50,
        column: params.get('column') || 'clicks',
        dir: params.get('dir') || 'desc',
      }
    }
  },
  data() {
    let params = (new URL(document.location)).searchParams;
    return {
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
      widgets: {},
      contents: {},
      adGroups: {},
      publishers: {},
      domains: {},
      rules: {},
      performance: null,
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
        { label: 'ID', name: 'id', orderable: true }, {
          label: 'Name',
          name: 'name',
          component: NameComponent,
          orderable: true
        }, {
          label: 'Image',
          name: 'image',
          component: ImageComponent,
          orderable: false
        }, {
          label: 'Actions',
          name: 'actions',
          orderable: false,
          classes: {
            'btn': true,
            'btn-primary': false,
            'btn-sm': true,
            'btn-clone-ad': true
          },
          event: 'click',
          handler: this.updateAdStatus,
          component: ActionsComponent
        },
        { label: 'Status', name: 'status', orderable: true },
        { label: 'Payout', name: 'payout', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'LP Views', name: 'lp_views', orderable: true },
        { label: 'LP Clicks', name: 'lp_clicks', orderable: true },
        { label: 'Conv.', name: 'conversions', orderable: true },
        { label: 'Actions', name: 'total_actions', orderable: true },
        { label: 'Actions CR', name: 'total_actions_cr', orderable: true },
        { label: 'CR', name: 'cr', orderable: true },
        { label: 'Revenue', name: 'total_revenue', orderable: true },
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
        { label: 'Name', name: 'name', orderable: true }, {
          label: 'Actions',
          name: 'actions',
          orderable: false,
          classes: {
            'btn': true,
            'btn-primary': false,
            'btn-sm': true,
            'btn-add-new-ad': true
          },
          event: 'click',
          handler: this.updateAdGroupStatus,
          component: ActionsComponent
        },
        { label: 'Status', name: 'status', orderable: true },
        { label: 'Impressions', name: 'impressions', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'Cost', name: 'cost', orderable: true }
      ],
      publisherColumns: [
        { label: 'ID', name: 'id', orderable: true },
        { label: 'Sub3', name: 'sub3', orderable: true },
        { label: 'Sub7', name: 'sub7', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'LP Views', name: 'lp_views', orderable: true },
        { label: 'LP Clicks', name: 'lp_clicks', orderable: true },
        { label: 'Pre LP Clicks', name: 'prelp_clicks', orderable: true },
        { label: 'LP CTR', name: 'lp_ctr', orderable: true },
        { label: 'Purchase', name: 'conversions', orderable: true },
        { label: 'CR', name: 'cr', orderable: true },
        { label: 'Total Actions', name: 'total_actions', orderable: true },
        { label: 'TR', name: 'tr', orderable: true },
        { label: 'Purchase Revenue', name: 'conversion_revenue', orderable: true },
        { label: 'Total Revenue', name: 'total_revenue', orderable: true },
        { label: 'Cost', name: 'cost', orderable: true },
        { label: 'Profit', name: 'profit', orderable: true },
        { label: 'ROI', name: 'roi', orderable: true },
        { label: 'CPC', name: 'cpc', orderable: true },
        { label: 'CPA', name: 'cpa', orderable: true },
        { label: 'EPC', name: 'epc', orderable: true }
      ],
      domainColumns: [
        { label: 'ID', name: 'id', orderable: true },
        { label: 'Domain ID', name: 'sub1', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'LP Views', name: 'lp_views', orderable: true },
        { label: 'LP Clicks', name: 'lp_clicks', orderable: true },
        { label: 'Pre LP Clicks', name: 'prelp_clicks', orderable: true },
        { label: 'LP CTR', name: 'lp_ctr', orderable: true },
        { label: 'Conv.', name: 'conversions', orderable: true },
        { label: 'CR', name: 'cr', orderable: true },
        { label: 'Actions', name: 'total_actions', orderable: true },
        { label: 'TR', name: 'tr', orderable: true },
        { label: 'Conversion Revenue', name: 'conversion_revenue', orderable: true },
        { label: 'Revenue', name: 'total_revenue', orderable: true },
        { label: 'Cost', name: 'cost', orderable: true },
        { label: 'Profit', name: 'profit', orderable: true },
        { label: 'ROI', name: 'roi', orderable: true },
        { label: 'CPC', name: 'cpc', orderable: true },
        { label: 'CPA', name: 'cpa', orderable: true },
        { label: 'EPC', name: 'epc', orderable: true }
      ],
      ruleColumns: [
        { label: 'ID', name: 'id', orderable: true }, {
          label: 'Actions',
          name: 'actions',
          orderable: false,
          classes: {
            'btn': true,
            'btn-primary': false,
            'btn-sm': true,
            'is-rule': true
          },
          component: ActionsComponent
        },
        { label: 'Name', name: 'name', orderable: true },
        { label: 'Action Name', name: 'action_name', orderable: true },
        { label: 'Status', name: 'status', orderable: true }
      ],
      summaryData: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      },
      selectedTracker: 'redtrack',
      targetDate: {
        start: this.$moment().format('YYYY-MM-DD'),
        end: this.$moment().format('YYYY-MM-DD')
      },
      show: 0,
      isLoading: false
    }
  },
  methods: {
    getShortcut() {
      const params = (new URL(document.location)).searchParams;
      const selectedShortcut = this.shortcuts.find(shortcut => shortcut.value === params.get('shortcut'));
      if (selectedShortcut) {
        return selectedShortcut.key
      }
      return 'today'
    },
    getData() {
      this.getSummaryData();
      if (this.selectedTracker) {
        this.domainColumns[1].name = 'sub1';
      } else {
        this.domainColumns[1].name = 'top_domain';
      }
      switch (location.hash) {
        case '#contents':
          this.getContentData();
          break;
        case '#ad-groups':
          this.getAdGroupData();
          break;
        case '#publishers':
          this.getPublisherData();
          break;
        case '#domains':
          this.getDomainData();
          break;
        case '#rules':
          this.getRuleData();
          break;
        case '#performance':
          this.getPerformanceData();
          break;
        default:
          if ([2, 3, 4, 5].includes(this.campaign.provider_id)) {
            this.getContentData();
          } else {
            this.getWidgetData();
          }
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
    reloadPublisherData(tableProps) {
      this.getPublisherData(tableProps);
    },
    reloadDomainData(tableProps) {
      this.getDomainData(tableProps);
    },
    reloadRuleData(tableProps) {
      this.getRuleData(tableProps);
    },
    getSummaryData() {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/summary`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker } }
        })
        .then((response) => {
          this.summaryData = response.data.summary_data;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
        });
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
          history.replaceState(undefined, undefined, "#widgets");
          this.show = 0;
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
          history.replaceState(undefined, undefined, "#contents");
          this.show = 1;
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
          history.replaceState(undefined, undefined, "#ad-groups");
          this.show = 2;
        });
    },
    getPublisherData(options = this.tableProps) {
      this.isLoading = true;
      axios.get('/campaigns/' + this.campaign.id + '/publishers', {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.publishers = response.data
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
          history.replaceState(undefined, undefined, "#publishers");
          this.show = 2;
        });
    },
    getDomainData(options = this.tableProps) {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/domains`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.domains = response.data;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
          history.replaceState(undefined, undefined, "#domains");
          this.show = 3;
        });
    },
    getRuleData(options = this.tableProps) {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/rules`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker }, ...options }
        })
        .then((response) => {
          this.rules = response.data;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
          history.replaceState(undefined, undefined, "#rules");
          this.show = 4;
        });
    },
    getPerformanceData() {
      this.isLoading = true;
      axios.get(`/campaigns/${this.campaign.id}/performance`, {
          params: {...this.targetDate, ... { tracker: this.selectedTracker } }
        })
        .then((response) => {
          this.fillData(response.data);
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          this.isLoading = false;
          history.replaceState(undefined, undefined, "#performance");
          this.show = 5;
        });
    },
    fillData(dataByDate) {
      let dates = []
      let currDate = this.$moment.utc(new Date(this.targetDate.start)).startOf('day')
      let lastDate = this.$moment.utc(new Date(this.targetDate.end)).startOf('day')
      do {
        dates.push(currDate.clone().format('YYYY-MM-DD'))
      } while (currDate.add(1, 'days').diff(lastDate) < 0)
      dates.push(currDate.clone().format('YYYY-MM-DD'))

      let datasets = []

      if (this.selectedTracker) {
        datasets = [{
          label: 'Profit',
          backgroundColor: 'rgb(32, 168, 216)',
          data: []
        }, {
          label: 'Clicks',
          backgroundColor: 'rgb(248, 185, 76)',
          data: []
        }, {
          label: 'Roi',
          backgroundColor: 'rgb(122, 193, 81)',
          data: []
        }, {
          label: 'Revenue',
          backgroundColor: 'rgb(252, 87, 89)',
          data: []
        }, {
          label: 'Cost',
          backgroundColor: 'rgb(229, 84, 193)',
          data: []
        }]
        _.each(dates, (date, index) => {
          datasets[0].data.push(0)
          datasets[1].data.push(0)
          datasets[2].data.push(0)
          datasets[3].data.push(0)
          datasets[4].data.push(0)
          _.each(dataByDate, (data, i) => {
            if (data.date === date) {
              datasets[0].data[index] = data.total_net
              datasets[1].data[index] = data.total_clicks
              datasets[2].data[index] = data.roi
              datasets[3].data[index] = data.total_revenue
              datasets[4].data[index] = data.total_cost
            }
          })
        })
      } else {
        datasets = [{
          label: 'Impressions',
          backgroundColor: 'rgb(32, 168, 216)',
          data: []
        }, {
          label: 'Clicks',
          backgroundColor: 'rgb(248, 185, 76)',
          data: []
        }, {
          label: 'Cost',
          backgroundColor: 'rgb(122, 193, 81)',
          data: []
        }]
        _.each(dates, (date, index) => {
          datasets[0].data.push(0)
          datasets[1].data.push(0)
          datasets[2].data.push(0)
          _.each(dataByDate, (data, i) => {
            if (data.day === date) {
              datasets[0].data[index] = data.total_impressions
              datasets[1].data[index] = data.total_clicks
              datasets[2].data[index] = data.total_cost
            }
          })
        })
      }

      this.performance = {
        labels: dates,
        datasets: datasets
      }
    },
    updateAdGroupStatus(data) {
      this.isLoading = true;
      axios.post(`/campaigns/${data.campaign_id}/ad-groups/${data.ad_group_id}/status`, {
        status: data.status
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
    updateAdStatus(data) {
      this.isLoading = true;
      axios.post(`/campaigns/${data.campaign_id}/ad-groups/${data.ad_group_id}/ads/status/${data.ad_id}`, {
        status: data.status
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
  border: 1px dashed #dee2e6;
}
</style>
