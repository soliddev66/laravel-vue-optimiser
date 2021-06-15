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
                      <div class="btn-group mr-3" role="group">
                        <div class="dropdown">
                          <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-plus"></i> Create
                          </button>
                          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a class="dropdown-item" :href="`/rule-templates/create?action=${ruleAction.id}`" v-for="ruleAction in ruleActions" :key="ruleAction.id">{{ ruleAction.name }}</a>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <table ref="ruleTemplatesTable" id="ruleTemplatesTable" class="table table-bordered table-hover text-center">
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
                    <a class="btn btn-sm btn-default border" :href="'/rule-templates/status/' + rule.id" @click.prevent="updateRuleTemplateStatus">
                      <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': rule.status == 'ACTIVE' }"></i>
                    </a>
                  </td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rule-templates/edit/' + rule.id"><i class="fas fa-edit"></i></a>
                  </td>
                  <td class="px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rule-templates/delete/' + rule.id" @click.prevent="deleteRule"><i class="fas fa-trash"></i></a>
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
    },
    ruleActions: {
      type: Array,
      default: []
    }
  },
  components: {
    Loading
  },
  mounted() {
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
    },
    deleteRule(e) {
      if (confirm('Are you sure to delete this rule template?')) {
        this.isLoading = true;
        axios.post(e.target.getAttribute('href'))
          .then(response => {
            if (response.data.errors) {
              alert(response.data.errors[0])
            } else {
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
              });
              alert('Delete the rule template successfully!');
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
    updateRuleTemplateStatus(e) {
      this.isLoading = true;
      axios.post(e.target.getAttribute('href'))
        .then((response) => {
          if (response.data.errors) {
            alert(response.data.errors[0])
          } else {
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
