<template>
  <div class="container">
    <div class="row justify-content-end mb-3">
      <div class="col-md-6 col-12">
        Welcome to Optimiser
      </div>
      <div class="col-md-6 col-12">
        <VueCtkDateTimePicker v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Cost</span>
            <span class="info-box-number">{{ round(summaryData.total_cost, 2) || 0 }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">NET</span>
            <span class="info-box-number">{{ round(summaryData.total_net, 2) || 0 }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Clicks</span>
            <span class="info-box-number">{{ round(summaryData.total_clicks, 2) || 0 }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">CPA</span>
            <span class="info-box-number">{{ round(summaryData.cpa, 2) || 0 }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Revenue</span>
            <span class="info-box-number">{{ round(summaryData.total_revenue, 2) || 0 }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">ROI</span>
            <span class="info-box-number">{{ round(summaryData.roi, 2) || 0 }}%</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Conversions</span>
            <span class="info-box-number">{{ round(summaryData.total_conversions, 2) || 0 }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">EPC</span>
            <span class="info-box-number">{{ round(summaryData.epc, 2) || 0 }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-end mb-3">
      <div class="col-12">
        <div class="card">
          <div class="card-body">
            <line-chart v-if="dataByDate" :chart-data="dataByDate"></line-chart>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center mb-3">
      <div class="col-md-12 col-12">
        <h3>Performace by Traffic Source</h3>
      </div>
      <div class="col-md-6 col-12 d-none">
        <select class="form-control" v-model="selectedProvider">
          <option value="">Traffic Source</option>
          <option v-for="provider in providers" :value="provider.slug">{{ provider.label }}</option>
        </select>
      </div>
      <div class="col-12 mt-3">
        <div class="card">
          <div class="card-body table-responsive">
            <table class="table table-bordered table-hover text-center">
              <thead class="border text-bold">
                <tr>
                  <th>Name</th>
                  <th>Imp.</th>
                  <th>TR Clicks</th>
                  <th>Cost</th>
                  <th>Rev.</th>
                  <th>NET</th>
                  <th>ROI</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="data in dataByProvider">
                  <td>{{ providerName(data.provider_id) }}</td>
                  <td>{{ round(summaryData.total_views, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_clicks, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_cost, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_revenue, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_net, 2) || 0 }}</td>
                  <td>{{ round(summaryData.roi, 2) || 0 }}%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center mb-3">
      <div class="col-md-12 col-12">
        <h3>TOP Winners/Losers</h3>
      </div>
      <div class="col-md-6 col-12 d-none">
        <select class="form-control">
          <option value="total_net">NET</option>
        </select>
      </div>
      <div class="col-6 mt-3">
        <div class="card">
          <div class="card-body table-responsive">
            <table class="table table-bordered table-hover text-center">
              <thead class="border text-bold">
                <tr>
                  <th>Name</th>
                  <th>Imp.</th>
                  <th>TR Clicks</th>
                  <th>Cost</th>
                  <th>Rev.</th>
                  <th>NET</th>
                  <th>ROI</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="data in topWinners">
                  <td>{{ providerName(data.provider_id) }}</td>
                  <td>{{ round(summaryData.total_views, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_clicks, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_cost, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_revenue, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_net, 2) || 0 }}</td>
                  <td>{{ round(summaryData.roi, 2) || 0 }}%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="col-6 mt-3">
        <div class="card">
          <div class="card-body table-responsive">
            <table class="table table-bordered table-hover text-center">
              <thead class="border text-bold">
                <tr>
                  <th>Name</th>
                  <th>Imp.</th>
                  <th>TR Clicks</th>
                  <th>Cost</th>
                  <th>Rev.</th>
                  <th>NET</th>
                  <th>ROI</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="data in topLosers">
                  <td>{{ providerName(data.provider_id) }}</td>
                  <td>{{ round(summaryData.total_views, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_clicks, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_cost, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_revenue, 2) || 0 }}</td>
                  <td>{{ round(summaryData.total_net, 2) || 0 }}</td>
                  <td>{{ round(summaryData.roi, 2) || 0 }}%</td>
                </tr>
              </tbody>
            </table>
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
import LineChart from '../plugins/LineChart.js';

import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

export default {
  props: {
    providers: {
      type: Array,
      default: []
    }
  },
  components: {
    VueCtkDateTimePicker,
    LineChart,
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.getData()
  },
  data() {
    return {
      selectedProvider: 'yahoo',
      targetDate: {
        start: this.$moment().subtract('30', 'days').format('YYYY-MM-DD'),
        end: this.$moment().format('YYYY-MM-DD')
      },
      summaryData: {
        total_cost: 0,
        total_net: 0,
        total_clicks: 0,
        cpa: 0,
        total_revenue: 0,
        roi: 0,
        total_conversions: 0,
        epc: 0
      },
      dataByDate: null,
      dataByProvider: [],
      topWinners: [],
      topLosers: []
    }
  },
  methods: {
    round(value) {
      return _.round(value, 2)
    },
    providerName(providerId) {
      return this.providers.find(provider => provider.id === providerId) ? this.providers.find(provider => provider.id === providerId).label : 'N/A'
    },
    getData() {
      axios.get('/home', {
          params: this.targetDate
        }).then((response) => {
          this.summaryData = response.data.summary_data
          this.dataByProvider = response.data.data_by_provider
          this.topWinners = response.data.top_winners
          this.topLosers = response.data.top_losers
          this.fillData(response.data.data_by_date)
        })
        .catch((err) => {
          alert(err);
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

      let datasets = [{
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

      this.dataByDate = {
        labels: dates,
        datasets: datasets
      }
      console.log(this.dataByDate)
    }
  }
}
</script>

<style>
</style>
