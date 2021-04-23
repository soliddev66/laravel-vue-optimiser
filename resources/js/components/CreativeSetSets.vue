<template>
  <section>
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
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in data" :key="item.id">
                  <td>{{ item.id }}</td>
                  <td>{{ item.name }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
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
    },
    adType: {
      type: String,
      default: 'image'
    }
  },
  components: {
    Loading
  },

  watch: {
    type: function () {
      this.getData()
    },
    adType: function () {
      this.getData()
    },
  },

  mounted() {
    console.log('Component mounted.')
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
      return axios.get(`/creatives/data?type=${this.type}&adType=${this.adType}`)
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

  }
}
</script>
