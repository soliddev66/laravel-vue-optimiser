<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="false" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body table-responsive">
            <div class="row">
              <div class="col">
                <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Dropdown button
                    </button>
                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>
                    </div>
                </div>

              </div>
            </div>
            <table ref="rulesTable" id="rulesTable" class="table table-bordered table-hover text-center">
              <thead>
                <tr>
                  <th>ID</th>
                  <th colspan="4">Actions</th>
                  <th>Camp. ID</th>
                  <th>Name</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="rule in data" :key="rule.id">
                  <td>{{ rule.id }}</td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rules/status/' + rule.id">
                      <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': rule.status == 'ACTIVE' }"></i>
                    </a>
                  </td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rules/edit/' + rule.id"><i class="fas fa-edit"></i></a>
                  </td>
                  <td class="border-right-0 px-1">
                    <a class="btn btn-sm btn-default border"><i class="fas fa-clone"></i></a>
                  </td>
                  <td class="px-1">
                    <a class="btn btn-sm btn-default border" :href="'/rules/delete/' + rule.id"><i class="fas fa-trash"></i></a>
                  </td>
                  <td></td>
                  <td><a :href="'/rules/' + rule.id"></a></td>
                  <td v-switch="rule.status">
                    <span v-case="'ACTIVE'" class="text-success">{{ rule.status }}</span>
                    <span v-case="'PAUSED'" class="text-danger">{{ rule.status }}</span>
                    <span v-default>{{ rule.status }}</span>
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
      isLoading: false,
      fullPage: true
    }
  },
  methods: {
    getData() {
    }
  }
}
</script>

<style>
</style>
