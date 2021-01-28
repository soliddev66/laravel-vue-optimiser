<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <data-table :data="logs" :columns="logColumns" @on-table-props-changed="reloadData"></data-table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Loading from 'vue-loading-overlay';
import 'vue-loading-overlay/dist/vue-loading.css';

export default {
  props: {
    rule: {
      type: Object,
      default: null
    }
  },
  components: {
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.getData(this.tableProps, true)
  },
  watch: {
    //
  },
  data() {
    return {
      logs: {},
      tableProps: {
        page: params.get('page') || 1,
        search: params.get('search') || '',
        length: params.get('length') || 10,
        column: params.get('column') || 'id',
        dir: params.get('dir') || 'desc',
      },
      logColumns: [{
        label: 'ID',
        name: 'id',
        orderable: true,
      }, {
        label: 'Traffic Source',
        name: 'provider_id',
        orderable: false,
      }, {
        label: 'Camp. ID',
        name: 'log_id',
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
        label: 'Payout',
        name: 'payout',
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
        label: 'Conversion',
        name: 'total_conversions',
        orderable: true,
      }, {
        label: 'Total Actions',
        name: 'total_actions',
        orderable: true,
      }, {
        label: 'Total Actions CR',
        name: 'total_actions_cr',
        orderable: true,
      }, {
        label: 'CR',
        name: 'cr',
        orderable: true,
      }, {
        label: 'Total Revenue',
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
      }],
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    getData(options = this.tableProps) {
      axios.get(`/rules/${this.rule.id}/logs/data`, { params: options })
        .then((response) => {
          this.logs = response.data;
        })
        .catch((err) => {
          alert(err);
        }).finally(() => {
          //
        })
    },
    reloadData(tableProps) {
      this.getData(tableProps);
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
</style>
