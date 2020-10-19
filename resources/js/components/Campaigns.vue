<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <div class="row">
              <div class="col-6">
                <VueCtkDateTimePicker v-model="targetDate" format="YYYY-MM-DD" formatted="YYYY-MM-DD" :range="true" @is-hidden="getData"></VueCtkDateTimePicker>
              </div>
            </div>
          </div>
          <div class="card-body table-responsive">
            <table id="campaignsTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th colspan="3">Actions</th>
                  <th>Camp. ID</th>
                  <th>Name</th>
                  <th>Status</th>
                  <th>Budget</th>
                  <th>Avg. CPC</th>
                  <th>Payout</th>
                  <th>Cost</th>
                  <th>Live Spent</th>
                  <th>TR Conv.</th>
                  <th>TS Clicks</th>
                  <th>TRK Clicks</th>
                  <th>LP Clicks</th>
                  <th>TS NET</th>
                  <th>TS ROI</th>
                  <th>eCPM</th>
                  <th>LP CR</th>
                  <th>LP CPC</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="campaign in data" :key="campaign.id">
                  <td>{{ campaign.id }}</td>
                  <td class="border-right-0">
                    <a class="btn btn-sm btn-info" :href="'/campaigns/' + campaign.id">View</a>
                  </td>
                  <td class="border-right-0">
                    <a class="btn btn-sm btn-primary" :href="'/campaigns/edit/' + campaign.id">Update</a>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-danger" :href="'/campaigns/delete/' + campaign.id" @click.prevent="deleteCampaign">Delete</a>
                  </td>
                  <td>{{ campaign.campaign_id }}</td>
                  <td>{{ campaign.name }}</td>
                  <td>{{ campaign.status }}</td>
                  <td>{{ campaign.budget }}</td>
                  <td>{{ avg(campaign.redtrack_report, 'cpc') || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'revenue') / count(campaign.redtrack_report, 'conversions')) || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'cost') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'cost') || 0 }}</td>
                  <td>{{ avg(campaign.redtrack_report, 'ctr') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'clicks') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'prelp_clicks') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'lp_clicks') || 0 }}</td>
                  <td>{{ count(campaign.redtrack_report, 'revenue') - count(campaign.redtrack_report, 'cost') || 0 }}</td>
                  <td>{{ avg(campaign.redtrack_report, 'roi') || 0 }}</td>
                  <td>{{ round((count(campaign.redtrack_report, 'revenue') / count(campaign.redtrack_report, 'lp_views')) * 1000) || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'conversions') / count(campaign.redtrack_report, 'lp_clicks')) || 0 }}</td>
                  <td>{{ round(count(campaign.redtrack_report, 'cost') / count(campaign.redtrack_report, 'lp_clicks')) || 0 }}</td>
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
import Loading from 'vue-loading-overlay'
import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    campaigns: {
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
    this.data = this.campaigns
  },
  data() {
    return {
      data: [],
      targetDate: {
        start: new Date(),
        end: new Date()
      },
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    avg(array, key) {
      return _.round(_.meanBy(array, (value) => value[key]), 2)
    },
    count(array, key) {
      return _.round(_.sumBy(array, (value) => value[key]), 2)
    },
    round(value) {
      return _.round(value, 2)
    },
    getData() {
      axios.post('/campaigns/search', this.targetDate)
        .then((response) => {
          this.data = response.data.campaigns
        })
        .catch((err) => {
          alert(err);
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
</style>
