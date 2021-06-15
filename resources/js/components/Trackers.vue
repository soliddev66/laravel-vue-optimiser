<template>
  <div class="container-fluid">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <table id="trackersTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Acc. ID</th>
                  <th>Name</th>
                  <th>Status</th>
                  <th>Provider</th>
                  <th>Platform</th>
                  <th>Actions</th>
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
                  <td>
                    <button class="btn btn-danger" @click="removeTracker(tracker)">Unlink</button>
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
  },
  data() {
    return {
      //
    }
  },
  methods: {
    removeTracker(tracker) {
      console.log('asdas');
      axios.post(`/trackers/remove`, {
          providerId: tracker.provider_id,
          openId: tracker.open_id
        })
        .then((response) => {
          alert('Tracker has been removed!');
          location.reload();
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
