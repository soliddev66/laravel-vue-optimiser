<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <data-table :data="logs" :columns="logColumns" @on-table-props-changed="reloadData" :order-by="tableProps.column" :order-dir="tableProps.dir"></data-table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash';
import Loading from 'vue-loading-overlay';
import LogDataComponent from './includes/LogDataComponent.vue';
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
    this.getData(this.tableProps)
  },
  watch: {
    //
  },
  data() {
    return {
      logs: {},
      tableProps: {
        page: 1,
        search: '',
        length: 10,
        column: 'id',
        dir: 'desc',
      },
      logColumns: [{
        label: 'ID',
        name: 'id',
        orderable: true,
      }, {
        label: 'Passed',
        name: 'passed',
        orderable: true,
      }, {
        label: 'Rule ID',
        name: 'rule_id',
        orderable: true,
      }, {
        label: 'Data',
        name: 'data',
        component: LogDataComponent,
        orderable: false,
      }, {
        label: 'Created At',
        name: 'created_at',
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
