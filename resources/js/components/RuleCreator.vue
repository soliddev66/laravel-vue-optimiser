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
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="ruleName" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="rule_group" class="col-sm-2 control-label mt-2">Rule Group</label>
                  <div class="col-sm-4">
                    <select name="rule_group" class="form-control" v-model="selectedRuleGroup">
                      <option value="">Select Group</option>
                      <option :value="ruleGroup.id" v-for="ruleGroup in ruleGroups" :key="ruleGroup.id">{{ ruleGroup.name }}</option>
                    </select>
                  </div>
                  <div class="col-sm-2" v-if="!saveRuleGroup">
                    <input type="text" name="rule_group_name" v-model="ruleGroupName" class="form-control" placeholder="Enter rule group name...">
                  </div>
                  <div class="col-sm-2" v-if="saveRuleGroup">
                    <button type="button" class="btn btn-primary" @click.prevent="saveRuleGroup = !saveRuleGroup">Create New</button>
                  </div>
                  <div class="col-sm-1" v-if="!saveRuleGroup && ruleGroupName">
                    <button type="button" class="btn btn-success" @click.prevent="createRuleGroup()">Save</button>
                  </div>
                  <div class="col-sm-1" v-if="!saveRuleGroup">
                    <button type="button" class="btn btn-warning" @click.prevent="saveRuleGroup = !saveRuleGroup">Cancel</button>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="dataFrom" class="col-sm-2 control-label">Considering data from</label>
                  <div class="col-sm-3">
                    <select name="data_from" class="form-control" v-model="selectedDataFrom">
                      <option value="">Select</option>
                      <option value="1">Option 1</option>
                      <option value="2">Option 2</option>
                      <option value="3">Option 3</option>
                    </select>
                  </div>
                  <label for="exclude" class="col-sm-2 control-label">Exclude days from interval</label>
                  <div class="col-sm-3">
                    <select name="excluded_days" class="form-control" v-model="selectedExcludedDay">
                      <option value="">Select</option>
                      <option value="1">Option 1</option>
                      <option value="2">Option 2</option>
                      <option value="3">Option 3</option>
                    </select>
                  </div>
                </div>

                <h2 class="pd-2">Rule Conditions</h2>
                <fieldset class="mb-3">
                  <fieldset class="mb-2" v-for="(ruleCondition, index) in ruleConditions" :key="ruleCondition.id">
                    <div class="form-group row" v-for="(condition, indexY) in ruleCondition" :key="condition.id">
                      <div class="col-sm-4">
                        <div class="input-group mb-2">
                          <div class="input-group-prepend">
                            <div class="input-group-text">IF</div>
                          </div>
                          <select name="rule_condition_type" class="form-control" v-model="condition.type">
                            <option value="">Select Rule Condition Type</option>
                            <option :value="ruleConditionType.id" v-for="ruleConditionType in ruleConditionTypes" :key="ruleConditionType.id">{{ ruleConditionType.name }}</option>
                          </select>
                          <div class="input-group-append">
                            <div class="input-group-text">is</div>
                          </div>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="input-group mb-2">
                          <select name="rule_condition_operation" class="form-control" v-model="condition.operation">
                            <option value="">Select Operation</option>
                            <option value="1">Less than</option>
                            <option value="2">Greater than</option>
                            <option value="3">Equal</option>
                          </select>
                          <div class="input-group-append">
                            <div class="input-group-text">THAN</div>
                          </div>
                        </div>
                      </div>
                      <div class="col-sm-3">
                        <div class="input-group mb-2">
                          <input type="number" :name="`rule_condition_amount${index}`" class="form-control" v-model="condition.amount" />
                          <input type="number" :name="`rule_condition_unit${index}`" placeholder="..." class="form-control" v-model="condition.unit" />
                        </div>
                      </div>
                      <div class="col-sm-1">
                        <button type="button" class="btn btn-light" @click.prevent="removeAndRuleCondition(index, indexY)" v-if="indexY > 0"><i class="fa fa-minus"></i></button>
                      </div>
                    </div>

                    <div class="form-group row">
                      <div class="col">
                        <button type="button" class="btn btn-primary" @click="addAndRuleConditon(index)">AND</button>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col text-right mt-3">
                        <button class="btn btn-warning btn-sm" @click.prevent="removeOrRuleCondition(index)" v-if="index > 0">Remove</button>
                      </div>
                      </div>
                  </fieldset>

                  <div class="form-group row pt-2">
                    <div class="col">
                      <button type="button" class="btn btn-primary" @click="addOrRuleConditon">OR</button>
                    </div>
                  </div>
                </fieldset>

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
                    <select2 name="campaigns" v-model="ruleCampaigns" :options="campaignSelections" :settings="{ multiple: true }" />
                  </div>
                  <!-- <div class="col-sm-3">
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
                  </div> -->
                </div>

                <div class="form-group row">
                  <label for="group" class="col-sm-2 control-label">Run this rule every</label>
                  <div class="col-sm-5">
                    <input type="number" name="interval_amount" class="form-control" v-model="ruleIntervalAmount" />
                  </div>
                  <div class="col-sm-5">
                    <select name="interval_unit" class="form-control" v-model="ruleIntervalUnit">
                      <option value="1">Minutes</option>
                      <option value="2">Hours</option>
                      <option value="3">Days</option>
                      <option value="4">Weeks</option>
                      <option value="5">Months</option>
                      <option value="6">Years</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <div class="col">
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="alert" id="runType1" value="1" v-model="ruleRunType">
                      <label class="form-check-label" for="runType1">Alert Only</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="alert" id="runType2" value="2" v-model="ruleRunType">
                      <label class="form-check-label" for="runType2">Execute</label>
                    </div>
                    <div class="form-check form-check-inline">
                      <input class="form-check-input" type="radio" name="alert" id="runType3" value="3" v-model="ruleRunType">
                      <label class="form-check-label" for="runType3">Execute & Alert</label>
                    </div>
                  </div>
                </div>
              </form>
            </div>

            <div class="card-footer d-flex justify-content-end">
              <button type="button" class="btn btn-primary" :disabled="!ruleNameState || !selectedRuleGroupState || !selectedDataFromState || !ruleIntervalAmountState || !ruleIntervalUnitState || !ruleCampaignsState || !ruleConditionsState" @click.prevent="saveRule">Save</button>
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
    rule: {
      type: Object,
      default: null
    },
    campaigns: {
      type: Array,
      default: []
    },
    ruleGroups: {
      type: Array,
      default: []
    },
    ruleConditionTypes: {
      type: Array,
      default: []
    },
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    ruleNameState() {
      return this.ruleName !== ''
    },
    selectedRuleGroupState() {
      return this.selectedRuleGroup !== ''
    },
    selectedDataFromState() {
      return this.selectedDataFrom !== ''
    },
    selectedExcludedDayState() {
      return this.selectedExcludedDay !== ''
    },
    ruleIntervalAmountState() {
      return this.ruleIntervalAmount !== ''
    },
    ruleIntervalUnitState() {
      return this.ruleIntervalUnit !== ''
    },
    ruleCampaignsState() {
      return this.ruleCampaigns.length
    },
    ruleConditionsState() {
      for (let i = 0; i < this.ruleConditions.length; i++) {
        for (let j = 0; j < this.ruleConditions[i].length; j++) {
          if (!this.ruleConditions[i][j].type || !this.ruleConditions[i][j].operation || !this.ruleConditions[i][j].amount || !this.ruleConditions[i][j].unit) {
            return false
          }
        }
      }
      return true
    }
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
      saveRuleGroup: true,
      ruleName: '',
      ruleGroupName: '',
      selectedRuleGroup: '',
      selectedDataFrom: '',
      selectedExcludedDay: '',
      ruleIntervalAmount: '',
      ruleIntervalUnit: '',
      ruleRunType: 1,
      ruleCampaigns: [],
      campaignSelections: this.campaigns.map(campaign => {
        return {
          id: campaign.id,
          text: campaign.name
        }
      }),
      ruleConditions: [
        [
          {type: '', operation: '', amount: '', unit: ''}
        ]
      ]
    }
  },
  methods: {
    createRuleGroup () {
      this.isLoading = true
      axios.post('/rule-groups', {
        name: this.ruleGroupName
      }).then(response => {
         if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          alert('New rule group has been created!')
          this.ruleGroupName = ''
          this.saveRuleGroup = true
          this.getRuleGroups()
        }
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    getRuleGroups () {
      this.isLoading = true
      axios.get('/rule-groups/selection-data').then(response => {
        this.ruleGroups = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    addOrRuleConditon () {
      this.ruleConditions.push([
        {type: '', operation: '', amount: '', unit: ''}
      ])
    },
    removeOrRuleCondition (index) {
      this.ruleConditions.splice(index, 1);
    },
    addAndRuleConditon (index) {
      this.ruleConditions[index].push({type: '', operation: '', amount: '', unit: ''});
    },
    removeAndRuleCondition (index, indexY) {
      this.ruleConditions[index].splice(indexY, 1);
    },
    saveRule () {
      this.postData = {
        name: this.ruleName,
        ruleGroup: this.selectedRuleGroup,
        dataFrom: this.selectedDataFrom,
        excludedDay: this.selectedExcludedDay,
        ruleConditions: this.ruleConditions,
        ruleCampaigns: this.ruleCampaigns,
        ruleIntervalAmount: this.ruleIntervalAmount,
        ruleIntervalUnit: this.ruleIntervalUnit,
        ruleRunType: this.ruleRunType
      }

      axios.post('/rules', this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          alert('Save successfully!');
        }
      }).catch(error => {
        console.log(error)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>

<style>
.select2-container .select2-selection--single {
  min-height: 28px;
  height: auto;
}
</style>
