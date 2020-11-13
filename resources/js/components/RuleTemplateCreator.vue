<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
          <div class="card">
            <div class="card-header">
              <h2 class="mb-0">New Rule Template</h2>
            </div>
            <div class="card-body">
              <errors :errors="errors" v-if="errors.errors"></errors>
              <form class="form-horizontal">
                <h2 class="pb-2">General information</h2>
                <div class="form-group row">
                  <label for="" class="col-sm-2 control-label">Action</label>
                  <div class="col-sm-10">
                    <select name="rule_action" class="form-control" v-model="selectedRuleAction">
                      <option value="">Select Action</option>
                      <option :value="ruleAction.id" v-for="ruleAction in ruleActions" :key="ruleAction.id">{{ ruleAction.name }}</option>
                    </select>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Rule Name</label>
                  <div class="col-sm-10">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="ruleName" />
                  </div>
                </div>

                <div class="form-group row">
                  <label for="dataFrom" class="col-sm-2 control-label">Considering data from</label>
                  <div class="col-sm-3">
                    <select name="data_from" class="form-control" v-model="selectedDataFrom" @change="selectedDataFromChanged">
                      <option value="">Select</option>
                      <option :value="ruleDataFromOption.id" v-for="ruleDataFromOption in ruleDataFromOptions" :key="ruleDataFromOption.id" :data-excluded-day="ruleDataFromOption.excluded_day_id">{{ ruleDataFromOption.name }}</option>
                    </select>
                  </div>
                  <label for="exclude" class="col-sm-2 control-label">Exclude days from interval</label>
                  <div class="col-sm-3">
                    <select name="excluded_days" class="form-control" v-model="selectedExcludedDay" disabled="disabled">
                      <option value="">Select</option>
                      <option value="1">None</option>
                      <option value="2">Today</option>
                      <option value="3">Today & Yesterday</option>
                    </select>
                  </div>
                </div>

                <h2 class="pd-2">Rule Conditions</h2>
                <fieldset class="mb-3 p-3 rounded border">
                  <fieldset class="mb-3 p-3 rounded border" v-for="(ruleCondition, index) in ruleConditionData" :key="index">
                    <div class="form-group row" v-for="(condition, indexY) in ruleCondition" :key="indexY">
                      <div class="col-sm-4">
                        <div class="input-group">
                          <div class="input-group-prepend">
                            <div class="input-group-text">IF</div>
                          </div>
                          <select name="rule_condition_type" class="form-control" v-model="condition.rule_condition_type_id">
                            <option value="">Select Rule Condition Type</option>
                            <optgroup v-for="ruleConditionTypeGroup in ruleConditionTypeGroups" :label="ruleConditionTypeGroup.name" :key="ruleConditionTypeGroup.id">
                              <option :value="ruleConditionType.id" v-for="ruleConditionType in ruleConditionTypeGroup.options" :key="ruleConditionType.id">{{ ruleConditionType.name }}</option>
                            </optgroup>
                          </select>
                          <div class="input-group-append">
                            <div class="input-group-text">is</div>
                          </div>
                        </div>
                      </div>
                      <div class="col-sm-4">
                        <div class="input-group">
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
                        <div class="input-group">
                          <input type="number" :name="`rule_condition_amount${index}`" class="form-control" v-model="condition.amount" />
                          <select :name="`rule_condition_unit${index}`" class="form-control" v-model="condition.unit">
                            <option value="1">...</option>
                            <option value="2">%</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-sm-1">
                        <button type="button" class="btn btn-light" @click.prevent="removeAndRuleCondition(index, indexY)" v-if="indexY > 0"><i class="fa fa-minus"></i></button>
                      </div>
                    </div>

                    <div class="form-group row mb-0">
                      <div class="col">
                        <button type="button" class="btn btn-primary" @click="addAndRuleConditon(index)">AND</button>
                      </div>
                    </div>
                    <div class="row" v-if="index > 0">
                      <div class="col text-right">
                        <button class="btn btn-warning btn-sm" @click.prevent="removeOrRuleCondition(index)">Remove</button>
                      </div>
                      </div>
                  </fieldset>

                  <div class="form-group row mb-0">
                    <div class="col">
                      <button type="button" class="btn btn-primary" @click="addOrRuleConditon">OR</button>
                    </div>
                  </div>
                </fieldset>

                <div class="form-group row">
                  <label for="group" class="col-sm-2 control-label mt-2">Run this rule every</label>
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
              <button type="button" class="btn btn-primary" :disabled="!ruleNameState || !selectedDataFromState || !ruleIntervalAmountState || !ruleIntervalUnitState || !ruleConditionsState" @click.prevent="saveRule">Save</button>
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
    ruleActions: {
      type: Array,
      default: []
    },
    ruleActionId: {
      type: String,
      default: ""
    },
    ruleConditions: {
      type: Array,
      default: []
    },
    ruleConditionTypeGroups: {
      type: Array,
      default: []
    },
    ruleDataFromOptions: {
      type: Array,
      default: []
    },
    action: {
      type: String,
      default: 'create'
    }
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    ruleNameState() {
      return this.ruleName !== ''
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
    ruleConditionsState() {
      for (let i = 0; i < this.ruleConditionData.length; i++) {
        for (let j = 0; j < this.ruleConditionData[i].length; j++) {
          if (!this.ruleConditionData[i][j].rule_condition_type_id || !this.ruleConditionData[i][j].operation || !this.ruleConditionData[i][j].amount || !this.ruleConditionData[i][j].unit) {
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
    let tempRuleCondition = {rule_condition_type_id: '', operation: '', amount: '', unit: '1'};
    console.log(this.ruleActionId)
    return {
      errors: {},
      isLoading: false,
      fullPage: true,
      postData: {},
      ruleName: this.rule ? this.rule.name : '',
      selectedDataFrom: this.rule ? this.rule.from : '',
      selectedRuleAction: this.rule ? this.rule.rule_action_id : this.ruleActionId,
      selectedExcludedDay: this.rule ? this.rule.exclude_day : '',
      ruleIntervalAmount: this.rule ? this.rule.interval_amount : '',
      ruleIntervalUnit: this.rule ? this.rule.interval_unit : '',
      ruleRunType: this.rule ? this.rule.run_type : 1,
      ruleWidget: this.rule ? this.rule.widget : '',
      selectedWidgetIncluded: this.rule ? this.rule.is_widget_included : 1,
      tempRuleCondition: tempRuleCondition,
      ruleConditionData: this.ruleConditions || [[{...tempRuleCondition}]]
    }
  },
  methods: {
    addOrRuleConditon () {
      this.ruleConditionData.push([{...this.tempRuleCondition}])
    },
    removeOrRuleCondition (index) {
      this.ruleConditionData.splice(index, 1);
    },
    addAndRuleConditon (index) {
      this.ruleConditionData[index].push({...this.tempRuleCondition});
    },
    removeAndRuleCondition (index, indexY) {
      this.ruleConditionData[index].splice(indexY, 1);
    },
    selectedDataFromChanged (e) {
      this.selectedExcludedDay = e.target.options[e.target.selectedIndex].dataset.excludedDay
    },
    saveRule () {
      this.postData = {
        ruleName: this.ruleName,
        ruleAction: this.selectedRuleAction,
        ruleGroup: this.selectedRuleGroup,
        dataFrom: this.selectedDataFrom,
        excludedDay: this.selectedExcludedDay,
        ruleConditions: this.ruleConditionData,
        ruleCampaigns: this.ruleCampaignData,
        ruleIntervalAmount: this.ruleIntervalAmount,
        ruleIntervalUnit: this.ruleIntervalUnit,
        ruleRunType: this.ruleRunType
      }

      let url = '/rule-templates';

      if (this.action == 'edit') {
        url += '/update/' + this.rule.id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          alert('Save successfully!')
          this.errors = {}
        }
      }).catch(error => {
        this.errors = error.response.data
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