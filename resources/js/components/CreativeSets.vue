<template>
  <div class="container rules">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row mb-3">
      <div class="col">
        <div class="btn-toolbar" role="toolbar">
          <a href="/creatives/create?type=media" class="btn btn-primary mr-2"><i class="far fa-folder-open"></i> Create media sets</a>
          <a href="/creatives/create?type=title" class="btn btn-primary"><i class="far fa-folder-open"></i> Create title sets</a>
        </div>
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <errors :errors="errors" v-if="errors.errors"></errors>
            <table ref="creativeSetsTable" id="creativeSetsTable" class="table table-bordered table-hover text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="creativeSet in data" :key="creativeSet.id">
                  <td>{{ creativeSet.id }}</td>
                  <td>{{ creativeSet.name }}</td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border" :href="'/creatives/edit/' + creativeSet.id"><i class="fas fa-edit"></i> Edit</a>
                    <a class="btn btn-sm btn-default border" :href="'/creatives/delete/' + creativeSet.id" @click.prevent="deleteCreativeSet"><i class="fas fa-trash"></i> Delete</a>
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
import _ from 'lodash';
import Loading from 'vue-loading-overlay'
import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    creativeSets: {
      type: Array,
      default: []
    }
  },
  components: {
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.data = this.creativeSets
  },
  data() {
    return {
      data: [],
      errors: {},
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    getData() {
      this.isLoading = true;
      return axios.get('/creatives/data')
        .then(response => {
          this.data = response.data.creativeSets
        })
        .catch(error => {
          this.errors = error.response.data
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    deleteCreativeSet(e) {
      if (confirm('Are you sure to delete this creative sets?')) {
        this.isLoading = true;
        axios.post(e.target.getAttribute('href'))
          .then(response => {
            if (response.data.errors) {
              alert(response.data.errors[0])
            } else {
              this.getData().then(() => {
                $('#creativeSetsTable').DataTable({
                  retrieve: true,
                  paging: true,
                  ordering: true,
                  info: true,
                  stateSave: false,
                  autoWidth: false,
                  pageLength: 10,
                });
              });
              alert('Delete the creative sets successfully!');
            }
          })
          .catch(error => {
            this.errors = error.response.data;
          })
          .finally(() => {
            this.isLoading = false;
          });
      }
    }
  }
}
</script>
