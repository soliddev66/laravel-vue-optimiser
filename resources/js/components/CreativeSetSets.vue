<template>
  <div class="col">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col">
        <div class="card">
          <div class="card-body table-responsive">
            <table ref="creativeSetSetsTable" id="creativeSetSetsTable" class="table table-bordered table-hover text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Name</th>
                  <th>Type</th>
                  <th></th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in data" :key="item.id">
                  <td>{{ item.id }}</td>
                  <td>{{ item.name }}</td>
                  <td>{{ item.type == 1 ? 'IMAGE' : (item.type == 2 ? 'VIDEO' : (item.type == 3 ? 'TITLE' : 'DESCRIPTION')) }}</td>
                  <td>
                    <button :disabled="(item.type == 1 && type != 'image') || (item.type == 2 && type != 'video') || (item.type == 3 && type != 'title') || (item.type == 4 && type != 'description')" type="button" class="btn btn-primary" @click="selectCreativeSet(item)"><i class="fas fa-check"></i></button>
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
    type: {
      type: String,
      default: 'media'
    }
  },
  components: {
    Loading
  },

  watch: {

  },

  mounted() {
    console.log('Component mounted.')
    this.getData()
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
        .then(() => {
          $('#creativeSetSetsTable').DataTable({
            retrieve: true,
            paging: true,
            ordering: true,
            info: true,
            stateSave: false,
            autoWidth: false,
            pageLength: 10,
          });
        })
        .catch(error => {
          this.errors = error.response.data
        })
        .finally(() => {
          this.isLoading = false;
        });
    },

    selectCreativeSet(set) {
      this.$emit('selectCreativeSet', set)
    }
  }
}
</script>
