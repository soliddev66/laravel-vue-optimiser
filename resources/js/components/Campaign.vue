<template>
  <div class="container">
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
            <ul class="nav nav-pills mb-3" role="tablist">
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 0 }" id="widgets-tab" data-toggle="pill" href="#widgets" role="tab" aria-controls="widgets" aria-selected="true" @click.prevent="getWidgetData()">Widgets</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 1 }" id="contents-tab" data-toggle="pill" href="#contents" role="tab" aria-controls="contents" aria-selected="false" @click.prevent="getContentData()">Contents</a>
              </li>
              <li class="nav-item" v-if="campaign.provider_id !== 2">
                <a class="nav-link" :class="{ 'active': show === 2 }" id="ad-groups-tab" data-toggle="pill" href="#ad-groups" role="tab" aria-controls="ad-groups" aria-selected="false" @click.prevent="getAdGroupData()">Ad Groups</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 3 }" id="domains-tab" data-toggle="pill" href="#domains" role="tab" aria-controls="domains" aria-selected="false" @click.prevent="getDomainData()">Domains</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 4 }" id="rules-tab" data-toggle="pill" href="#rules" role="tab" aria-controls="rules" aria-selected="false">Rules</a>
              </li>
              <li class="nav-item">
                <a class="nav-link" :class="{ 'active': show === 5 }" id="performance-tab" data-toggle="pill" href="#performance" role="tab" aria-controls="performance" aria-selected="false">Performance</a>
              </li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade" :class="{ 'show active': show === 0 }" id="widgets" role="tabpanel" aria-labelledby="widgets-tab">
                <data-table :data="widgets" :columns="widgetColumns" @on-table-props-changed="reloadWidgetData"></data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 1 }" id="contents" role="tabpanel" aria-labelledby="contents-tab">
                <a class="btn btn-primary btn-sm mb-2" v-if="campaign.provider_id == 2" :href="`/campaigns/${campaign.id}/ad-groups/ad-group/ads/create`">Create</a>
                <data-table :data="contents" :columns="contentColumns" @on-table-props-changed="reloadContentData"></data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 2 }" id="ad-groups" role="tabpanel" aria-labelledby="ad-groups-tab">
                <data-table :data="adGroups" :columns="adGroupColumns" @on-table-props-changed="reloadAdGroupData"></data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 3 }" id="domains" role="tabpanel" aria-labelledby="domains-tab">
                <data-table :data="domains" :columns="domainColumns" @on-table-props-changed="reloadDomainData"></data-table>
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 4 }" id="rules" role="tabpanel" aria-labelledby="rules-tab">
                Rules
              </div>
              <div class="tab-pane fade" :class="{ 'show active': show === 5 }" id="performance" role="tabpanel" aria-labelledby="performance-tab">
                Performance
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
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.getSummaryData();
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
        { label: 'Name', name: 'name', orderable: true }, {
          label: 'Actions',
          name: 'actions',
          orderable: false,
          classes: {
            'btn': true,
            'btn-primary': false,
            'btn-sm': true,
          },
          event: "click",
          handler: this.updateAdStatus,
          component: ActionsComponent
        },
        { label: 'Status', name: 'status', orderable: true },
        { label: 'Payout', name: 'payout', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'LP Views', name: 'lp_views', orderable: true },
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
        { label: 'Name', name: 'name', orderable: true }, {
          label: 'Actions',
          name: 'actions',
          orderable: false,
          classes: {
            'btn': true,
            'btn-primary': false,
            'btn-sm': true,
            'btn-add-new-ad': true,
          },
          event: "click",
          handler: this.updateAdGroupStatus,
          component: ActionsComponent
        },
        { label: 'Status', name: 'status', orderable: true }
      ],
      domainColumns: [
        { label: 'ID', name: 'id', orderable: true },
        { label: 'Domain ID', name: 'sub1', orderable: true },
        { label: 'Clicks', name: 'clicks', orderable: true },
        { label: 'LP Views', name: 'lp_views', orderable: true },
        { label: 'LP Clicks', name: 'lp_clicks', orderable: true },
        { label: 'Pre LP Clicks', name: 'prelp_clicks', orderable: true },
        { label: 'LP CTR', name: 'lp_ctr', orderable: true },
        { label: 'Conversion', name: 'conversions', orderable: true },
        { label: 'CR', name: 'cr', orderable: true },
        { label: 'Total Actions', name: 'total_actions', orderable: true },
        { label: 'TR', name: 'tr', orderable: true },
        { label: 'Conversion Revenue', name: 'conversion_revenue', orderable: true },
        { label: 'Total Revenue', name: 'total_revenue', orderable: true },
        { label: 'Cost', name: 'cost', orderable: true },
        { label: 'Profit', name: 'profit', orderable: true },
        { label: 'ROI', name: 'roi', orderable: true },
        { label: 'CPC', name: 'cpc', orderable: true },
        { label: 'CPA', name: 'cpa', orderable: true },
        { label: 'EPC', name: 'epc', orderable: true }
      ],
      summaryData: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      },
      selectedTracker: 'redtrack',
      targetDate: {
        start: this.$moment().subtract(30, 'days').format('YYYY-MM-DD'),
        end: this.$moment().format('YYYY-MM-DD')
      },
      show: 0,
      isLoading: false,
      tableProps: {
        page: params.get('page') || 1,
        search: params.get('search') || '',
        length: params.get('length') || 10,
        column: params.get('column') || 'clicks',
        dir: params.get('dir') || 'desc',
      }
    }
  },
  methods: {
    getData() {
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
    reloadDomainData(tableProps) {
      this.getDomainData(tableProps);
    },
    getSummaryData() {
      console.log('asda');
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
