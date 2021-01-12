<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <h1>Pending Jobs: {{ size }}</h1>
            <hr>
            <h2>Failed Jobs</h2>
            <table id="failedJobsTable" class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Queue</th>
                  <th>Status</th>
                  <th>Payload</th>
                  <th>Exception</th>
                </tr>
              </thead>
              <tbody>
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
    size: Number
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
