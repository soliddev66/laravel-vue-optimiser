<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <table ref="ruleTemplatesTable" id="ruleTemplatesTable" class="table table-bordered table-hover text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="rule in data" :key="rule.id">
                  <td>{{ rule.id }}</td>
                  <td>{{ rule.name }}</td>
                  <td>
                    <a class="btn btn-sm btn-default border" :href="'/rules/create/' + rule.id"><i class="fas fa-plus"></i></a>
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
    rules: {
      type: Array,
      default: []
    }
  },
  components: {
  },
  mounted() {
    this.getData().then(() => {
      $('#ruleTemplatesTable').DataTable({
        retrieve: true,
        paging: true,
        ordering: true,
        info: true,
        stateSave: false,
        autoWidth: false,
        pageLength: 10,
      });
    })
  },
  data() {
    return {
      data: []
    }
  },
  methods: {
    getData() {
      this.isLoading = true;
      return axios.get('/rule-templates/data')
        .then(response => {
          this.data = response.data.rules
        })
        .catch(error => {
          this.errors = error.response.data
        })
        .finally(() => {
          this.isLoading = false;
        });
    }
  }
}
</script>
