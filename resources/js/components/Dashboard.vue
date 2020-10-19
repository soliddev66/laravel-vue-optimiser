<template>
  <div class="container">
    <div class="row justify-content-start mb-3">
      <div class="col-md-6 col-12">
        <VueCtkDateTimePicker v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Cost</span>
            <span class="info-box-number">{{ summaryData.total_cost }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">NET</span>
            <span class="info-box-number">{{ summaryData.total_net }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Clicks</span>
            <span class="info-box-number">{{ summaryData.total_clicks }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">CPA</span>
            <span class="info-box-number">{{ summaryData.avg_cpa }}</span>
          </div>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Revenue</span>
            <span class="info-box-number">{{ summaryData.total_revenue }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">ROI</span>
            <span class="info-box-number">{{ summaryData.avg_roi }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">Conversions</span>
            <span class="info-box-number">{{ summaryData.total_conversions }}</span>
          </div>
        </div>
      </div>
      <div class="col-md-3 col-sm-6 col-12">
        <div class="info-box">
          <div class="info-box-content">
            <span class="info-box-text">EPC</span>
            <span class="info-box-number">{{ summaryData.avg_epc }}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
import Loading from 'vue-loading-overlay';
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

export default {
  props: {
    data: {
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
    console.log(this.data)
    this.summaryData = this.data[0]
  },
  data() {
    return {
      targetDate: {
        start: new Date(),
        end: new Date()
      },
      summaryData: {
        total_cost: 0,
        total_net: 0,
        total_clicks: 0,
        avg_cpa: 0,
        total_revenue: 0,
        avg_roi: 0,
        total_conversions: 0,
        avg_epc: 0
      }
    }
  },
  methods: {
    getData() {
      axios.get('/home', {
          params: this.targetDate
        }).then((response) => {
          this.summaryData = response.data.data[0]
        })
        .catch((err) => {
          alert(err);
        });
    }
  }
}
</script>

<style>
</style>
