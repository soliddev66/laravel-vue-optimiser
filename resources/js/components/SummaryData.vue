<template>
  <div class="row justify-content-center">
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box">
        <div class="info-box-content">
          <span class="info-box-text">Total Cost</span>
          <span class="info-box-number">{{ round(summaryData.total_cost, 2) || 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box">
        <div class="info-box-content">
          <span class="info-box-text">Total Revenue</span>
          <span class="info-box-number">{{ round(summaryData.total_revenue, 2) || 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box">
        <div class="info-box-content">
          <span class="info-box-text">Total NET</span>
          <span class="info-box-number">{{ round(summaryData.total_net, 2) || 0 }}</span>
        </div>
      </div>
    </div>
    <div class="col-md-3 col-sm-6 col-12">
      <div class="info-box">
        <div class="info-box-content">
          <span class="info-box-text">Total ROI</span>
          <span class="info-box-number">{{ round(summaryData.avg_roi, 2) || 0 }}%</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    summaryData: {
      type: Object,
      default: {
        total_cost: 0,
        total_revenue: 0,
        total_net: 0,
        avg_roi: 0
      }
    }
  },
  mounted() {
    //
  },
  data() {
    return {
      //
    }
  },
  methods: {
    round(value) {
      if (value !== undefined) {
        return _.round(value, 2)
      }
      return 0
    },
  }
}
</script>

<style>
</style>
