<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <table id="trafficSourcesTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Account Name</th>
                  <th>Status</th>
                  <th>Traffic Source</th>
                  <th>Linked Tracker</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="trafficSource in trafficSources">
                  <td>{{ trafficSource.id }}</td>
                  <td>{{ trafficSource.open_id }}</td>
                  <td>Enabled</td>
                  <td>{{ providers.find(provider => provider.id === trafficSource.provider_id).label }}</td>
                  <td>{{ linkedTracker(trafficSource) }}</td>
                  <td>
                    <button class="btn btn-danger" @click.prevent="removeTrafficSource(trafficSource)">Unlink</button>
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
    providers: {
      type: Array
    },
    trafficSources: {
      type: Array
    },
    trackers: {
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
    linkedTracker(trafficSource) {
      const tracker = this.trackers.find(tracker => tracker.provider_open_id === trafficSource.open_id);
      if (tracker) {
        return tracker.name;
      }
      return '';
    },
    removeTrafficSource(trafficSource) {
      axios.post(`/traffic-sources/remove`, {
          providerId: trafficSource.provider_id,
          openId: trafficSource.open_id
        })
        .then((response) => {
          alert('Traffic source has been removed!');
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
