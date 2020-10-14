<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <table id="queuesTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Queue</th>
                  <th>Payload</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="queue in data">
                  <td>{{ queue.id }}</td>
                  <td>{{ queue.queue }}</td>
                  <td>{{ JSON.parse(queue.payload).displayName }}</td>
                  <td>{{ queue.failed_at ? 'Failed' : 'Pending' }}</td>
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
    queues: {
      type: Array,
      default: []
    },
    failed: {
      type: Array,
      default: []
    }
  },
  mounted() {
    console.log('Component mounted.')
    this.data = {...this.queues, ...this.failed }
  },
  data() {
    return {
      data: []
    }
  },
  methods: {
    //
  }
}
</script>

<style>
</style>
