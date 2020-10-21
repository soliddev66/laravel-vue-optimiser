<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">New Rule</h2>
            </div>
            <div class="card-body">
              <form class="form-horizontal">
                <h2 class="pb-2">General information</h2>
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Rule Name</label>
                  <div class="col-sm-10">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="group" class="col-sm-2 control-label mt-2">Rule Group</label>
                  <div class="col-sm-4">
                    <select name="group" class="form-control">
                      <option value="">Select Group</option>
                      <option>Sample</option>
                    </select>
                  </div>
                  <div class="col-sm-2">
                    <button type="button" class="btn btn-primary">Create New</button>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="dataFrom" class="col-sm-2 control-label">Considering data from</label>
                  <div class="col-sm-3">
                    <input type="number" name="dataFrom" class="form-control" />
                  </div>
                  <label for="exclude" class="col-sm-2 control-label">Exclude days from interval</label>
                  <div class="col-sm-3">
                    <input type="number" name="exclude" class="form-control" />
                  </div>
                </div>

                <h2 class="pd-2">Rule Conditions</h2>
                <div class="form-group row">
                  <div class="col-sm-3">
                    <div class="input-group mb-2">
                      <div class="input-group-prepend">
                        <div class="input-group-text">IF</div>
                      </div>
                      <select name="condition1" class="form-control">
                        <option value="">Select Group</option>
                        <option>Sample</option>
                      </select>
                      <div class="input-group-append">
                        <div class="input-group-text">is</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="input-group mb-2">
                      <input type="number" name="condition2" class="form-control" />
                      <div class="input-group-append">
                        <div class="input-group-text">THAN</div>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <div class="input-group mb-2">
                      <input type="text" name="name" class="form-control" />
                      <input type="number" name="condition2" placeholder="..." class="form-control" />
                    </div>
                  </div>
                  <div class="col-sm-3">
                    <button type="button" class="btn btn-light"><i class="fa fa-minus"></i></button>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col">
                    <button type="button" class="btn btn-primary">Add</button>
                  </div>
                </div>

                <div class="row">
                  <div class="col-6">
                    <h2 class="pb-2">Widget Filtering</h2>
                  </div>
                  <div class="col-6 text-right">
                    <button class="btn">Copy to clipboard</button>
                    <button class="btn">Clear widget</button>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col-sm-2">
                    <div class="btn-group mr-3" role="group">
                      <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          Select filter
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <a class="dropdown-item" href="#">Action</a>
                          <a class="dropdown-item" href="#">Another action</a>
                          <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-10">
                    <input type="text" name="widgets" placeholder="Add widgets (comma separated)" class="form-control" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="group" class="col-sm-2 control-label">Apply rule to campaigns</label>
                  <div class="col-sm-7">
                    <select name="group" class="form-control">
                      <option value="">Search campaigns...</option>
                      <option>Sample</option>
                    </select>
                  </div>
                  <div class="col-sm-3">
                    <div class="btn-group mr-3" role="group">
                      <div class="dropdown">
                        <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                          <i class="fa fa-filter"></i> Add campaigns
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                          <a class="dropdown-item" href="#">Action</a>
                          <a class="dropdown-item" href="#">Another action</a>
                          <a class="dropdown-item" href="#">Something else here</a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="group" class="col-sm-2 control-label">Run this rule every</label>
                  <div class="col-sm-10">
                    <input type="number" name="interval" class="form-control" />
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="alert" id="inlineRadio1" value="option1">
                      <label class="form-check-label" for="inlineRadio1">Alert Only</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="alert" id="inlineRadio2" value="option2">
                      <label class="form-check-label" for="inlineRadio2">Execute</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="alert" id="inlineRadio3" value="option3">
                      <label class="form-check-label" for="inlineRadio3">Execute & Alert</label>
                    </div>
                  </div>
                </div>
              </form>
            </div>

            <div class="card-footer d-flex justify-content-end">
              <button type="button" class="btn btn-primary">Save</button>
            </div>
          </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
  },
  components: {
    Loading,
    Select2
  },
  computed: {
  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {
  },
  data() {
    return {
      isLoading: false,
      fullPage: true,
      postData: {},
      options: [{ id: 1, text: "Hello" }, { id: 2, text: "World" }]
    }
  },
  methods: {
  }
}
</script>

<style>
.select2-container .select2-selection--single {
  min-height: 28px;
  height: auto;
}
</style>
