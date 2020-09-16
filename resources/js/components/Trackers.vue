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
                  <th>Account Name</th>
                  <th>Status</th>
                  <th>Tracking Platform</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="tracker in trackers">
                  <td>{{ tracker.id }}</td>
                  <td>{{ tracker.name }}</td>
                  <td>Enabled</td>
                  <td>{{ tracker.tracker_id }}</td>
                  <td>
                  </td>
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
      type: Array,
      default: []
    }
  },
  mounted() {
    console.log('Component mounted.')
    console.log(this.trackers)
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
