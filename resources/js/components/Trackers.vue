<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <table id="trackersTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Account ID</th>
                  <th>Account Name</th>
                  <th>Status</th>
                  <th>Tracking For</th>
                  <th>Platform</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tracker in trackers">
                  <td>{{ tracker.id }}</td>
                  <td>{{ tracker.open_id }}</td>
                  <td>{{ tracker.name }}</td>
                  <td>Enabled</td>
                  <td>{{ providers.find(provider => provider.id === tracker.provider_id).label }} - {{ tracker.provider_open_id }}</td>
                  <td>{{ trackersList.find(dbTracker => dbTracker.id === tracker.tracker_id).label }}</td>
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
export default {
  props: {
    trackers: {
      type: Array
    },
    providers: {
      type: Array
    },
    trackersList: {
      type: Array
    }
  },
  mounted() {
    console.log('Component mounted.')
  },
  data() {
    return {
      //
    }
  },
  methods: {
    //
  }
}
</script>

<style>
</style>
