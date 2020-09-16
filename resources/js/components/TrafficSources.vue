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
                  <th>Linked Trackers</th>
                  <th>Timezone</th>
                  <th>Currency</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="provider in providers">
                  <td>{{ provider.id }}</td>
                  <td>{{ provider.open_id }}</td>
                  <td>Enabled</td>
                  <td>{{ provider.provider_id }}</td>
                  <td></td>
                  <td></td>
                  <td></td>
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
    providers: {
      type: Array,
      default: []
    }
  },
  mounted() {
    console.log('Component mounted.')
    console.log(this.providers)
  },
  data() {
    return {
      //
    }
  },
  methods: {

  }
}
</script>

<style>
</style>
