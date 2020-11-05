<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <errors :errors="errors" v-if="errors.errors"></errors>
            <div class="row mb-3">
              <div class="col">
                <div class="btn-toolbar" role="toolbar">
                  <div class="btn-group mr-3" role="group">
                    <div class="btn-toolbar" role="toolbar">
                      <div class="btn-group" role="group">
                        <a href="/rules/create" class="btn btn-light"><i class="fa fa-plus"></i> Create</a>
                      </div>
                    </div>
                  </div>
                  <div class="btn-group" role="group">
                    <button type="button" class="btn btn-light" data-toggle="modal" data-target=".rule-template-modal"><i class="far fa-folder-open"></i> Create from template</button>
                  </div>
                </div>
              </div>
            </div>
            <table ref="rulesTable" id="rulesTable" class="table table-bordered table-hover text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <th colspan="3">Actions</th>
                  <th>Name</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="rule in data" :key="rule.id">
                  <td>{{ rule.id }}</td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rules/status/' + rule.id" @click.prevent="updateRuleStatus">
                      <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': rule.status == 'ACTIVE' }"></i>
                    </a>
                  </td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rules/edit/' + rule.id"><i class="fas fa-edit"></i></a>
                  </td>
                  <td class="px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rules/delete/' + rule.id" @click.prevent="deleteRule"><i class="fas fa-trash"></i></a>
                  </td>
                  <td>{{ rule.name }}</td>
                  <td>
                    <span v-if="rule.status === 'ACTIVE'" class="text-success">{{ rule.status }}</span>
                    <span v-if="rule.status === 'PAUSED'" class="text-danger">{{ rule.status }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade rule-template-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="col mt-3">
            <h1>Select Rule Template</h1>
          </div>
          <rule-rule-templates :rules="null"></rule-rule-templates>
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
    rules: {
      type: Array,
      default: []
    }
  },
  components: {
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.data = this.rules
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
      return axios.get('/rules/data')
        .then(response => {
          this.data = response.data.rules
        })
        .catch(error => {
          this.errors = error.response.data
        })
        .finally(() => {
          this.isLoading = false;
        });
    },
    deleteRule(e) {
      if (confirm('Are you sure to delete this rule?')) {
        this.isLoading = true;
        axios.post(e.target.getAttribute('href'))
          .then(response => {
            if (response.data.errors) {
              alert(response.data.errors[0])
            } else {
              this.getData().then(() => {
                $('#rulesTable').DataTable({
                  retrieve: true,
                  paging: true,
                  ordering: true,
                  info: true,
                  stateSave: false,
                  autoWidth: false,
                  pageLength: 10,
                });
              });
              alert('Delete the rule successfully!');
            }
          })
          .catch(error => {
            this.errors = error.response.data;
          })
          .finally(() => {
            this.isLoading = false;
          });
      }
    },
    updateRuleStatus(e) {
      this.isLoading = true;
      axios.post(e.target.getAttribute('href'))
        .then((response) => {
          if (response.data.errors) {
            alert(response.data.errors[0])
          } else {
            this.getData().then(() => {
              $('#rulesTable').DataTable({
                retrieve: true,
                paging: true,
                ordering: true,
                info: true,
                stateSave: false,
                autoWidth: false,
                pageLength: 10,
              });
            });
          }
        })
        .catch((err) => {
          this.errors = error.response.data;
        })
        .finally(() => {
          this.isLoading = false;
        });
    }
  }
}
</script>