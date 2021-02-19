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
              <errors :errors="errors" v-if="errors.errors"></errors>
              <form class="form-horizontal">
                <fieldset class="mb-4 p-3 rounded border bg-dark">
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
                        <option :value="ruleGroup.id" v-for="ruleGroup in ruleGroupData" :key="ruleGroup.id">{{ ruleGroup.name }}</option>
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
                    <div class="col">
                      <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="alert" id="runType1" value="1" v-model="ruleRunType">
                        <label class="form-check-label" for="runType1">Alert</label>
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

                  <div class="form-group row">
                    <label for="group" class="col-sm-2 control-label mt-2">Every</label>
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
                    <label for="" class="col-sm-2 control-label mt-2">Do</label>
                    <div class="col-sm-10">
                      <select name="rule_action" class="form-control" v-model="selectedRuleAction" @change="selectedRuleActionChanged">
                        <option :value="ruleAction.id" v-for="ruleAction in ruleActions" :key="ruleAction.id" :data-provider="ruleAction.provider">{{ ruleAction.name }}</option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group row">
                    <label for="dataFrom" class="col-sm-2 control-label mt-2">Considering data from</label>
                    <div class="col-sm-3">
                      <select name="data_from" class="form-control" v-model="selectedDataFrom" @change="selectedDataFromChanged">
                        <option value="">Select</option>
                        <option :value="ruleDataFromOption.id" v-for="ruleDataFromOption in ruleDataFromOptions" :key="ruleDataFromOption.id" :data-excluded-day="ruleDataFromOption.excluded_day_type">{{ ruleDataFromOption.name }}</option>
                      </select>
                    </div>
                    <label for="exclude" class="col-sm-2 control-label">Exclude days from interval</label>
                    <div class="col-sm-3">
                      <select name="excluded_days" class="form-control" v-model="selectedExcludedDay">
                        <option :value="excludedDayOption.id" v-for="excludedDayOption in excludedDayOptions" :key="excludedDayOption.id">{{ excludedDayOption.name }}</option>
                      </select>
                    </div>
                  </div>

                  <h3 class="pb-2">On</h3>
                  <fieldset class="mb-4 p-3 rounded border">
                    <component :is="ruleActionProvider" :submitData="ruleActionData" />
                  </fieldset>
                </fieldset>

                <fieldset class="mb-4 p-3 rounded border bg-dark">
                  <fieldset class="mb-3 p-3 rounded border bg-secondary" v-for="(ruleCondition, index) in ruleConditionData" :key="index">
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
                        <button type="button" class="btn btn-success" @click="addAndRuleConditon(index)">AND</button>
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
              </form>
            </div>

            <div class="card-footer d-flex justify-content-end">
              <button type="button" class="btn btn-primary" :disabled="!ruleNameState || !selectedRuleGroupState || !selectedDataFromState || !ruleIntervalAmountState || !ruleIntervalUnitState || !ruleConditionsState || !selectedWidgetIncludedState || !selectedRuleActionState || !ruleActionDataState" @click.prevent="saveRule">Save</button>
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

import {
  ChangeCampaignBudget,
  ActivateCampaign,
  PauseCampaign,
  PauseContents,
  BlockSite,
  BlockWidgetsPushlisher,
} from './rule-actions'

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
    ruleConditions: {
      type: Array,
      default: []
    },
    ruleGroups: {
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
    Select2,
    ChangeCampaignBudget,
    ActivateCampaign,
    PauseCampaign,
    PauseContents,
    BlockSite,
    BlockWidgetsPushlisher,
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
    selectedWidgetIncludedState() {
      return this.selectedWidgetIncluded !== ''
    },
    selectedRuleActionState() {
      return this.selectedRuleAction !== ''
    },
    ruleActionDataState() {
      return this.selectedRuleAction !== ''
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
    let tempRuleCondition = {rule_condition_type_id: '', operation: 1, amount: '', unit: '1'},
      excludedDayOptionType = this.rule.excluded_day_type ? this.rule.excluded_day_type : null;

    return {
      errors: {},
      isLoading: false,
      fullPage: true,
      postData: {},
      ruleGroupData: this.ruleGroups,
      saveRuleGroup: true,
      ruleName: this.rule ? this.rule.name : '',
      ruleGroupName: '',
      ruleWidget: this.rule.widget ? this.rule.widget : '',
      selectedRuleGroup: this.rule.rule_group_id ? this.rule.rule_group_id : '',
      selectedDataFrom: this.rule.from ? this.rule.from : '',
      selectedExcludedDay: this.rule.exclude_day ? this.rule.exclude_day : 1,
      excludedDayOptionType: excludedDayOptionType,
      excludedDayOptions: this.generateExcludeDayOptions(excludedDayOptionType),
      ruleIntervalAmount: this.rule.interval_amount ? this.rule.interval_amount : 1,
      ruleIntervalUnit: this.rule.interval_unit ? this.rule.interval_unit : 1,
      ruleRunType: this.rule.run_type ? this.rule.run_type : 1,
      selectedRuleAction: this.rule.rule_action_id ? this.rule.rule_action_id : 1,
      selectedWidgetIncluded: this.rule.is_widget_included ? this.rule.is_widget_included : 1,
      tempRuleCondition: tempRuleCondition,
      ruleConditionData: this.ruleConditions.length > 0 ? this.ruleConditions : [[{...tempRuleCondition}]],
      ruleActionProvider: this.rule.rule_action_provider ? this.rule.rule_action_provider : this.ruleActions[0].provider,
      ruleActionData: this.rule.rule_action_data ? JSON.parse(this.rule.rule_action_data) : {}
    }
  },
  methods: {
    generateExcludeDayOptions (type) {
      let excludedDayOptions = [
        {id: 1, name: 'None', selected: true}
      ]
      if (type == 2) {
        excludedDayOptions.push({id: 2, name: 'Today'})
      } else if (type == 3) {
        excludedDayOptions.push({id: 2, name: 'Today'})
        excludedDayOptions.push({id: 3, name: 'Today & Yesterday'})
      }

      return excludedDayOptions
    },
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
      }).catch(error => {
        if (error.response.status == 422) {
          this.errors = error.response.data
        }
      }).finally(() => {
        this.isLoading = false
      })
    },
    getRuleGroups () {
      this.isLoading = true
      axios.get('/rule-groups/selection-data').then(response => {
        this.ruleGroupData = response.data
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    addOrRuleConditon () {
      this.ruleConditionData.push([{...this.tempRuleCondition}])
    },
    removeOrRuleCondition (index) {
      this.ruleConditionData.splice(index, 1);
    },
    addAndRuleConditon (index) {
      this.ruleConditionData[index].push({...this.tempRuleCondition})
    },
    removeAndRuleCondition (index, indexY) {
      this.ruleConditionData[index].splice(indexY, 1)
    },
    selectedDataFromChanged (e) {
      this.excludedDayOptionType = e.target.options[e.target.selectedIndex].dataset.excludedDay
      this.excludedDayOptions = this.generateExcludeDayOptions(this.excludedDayOptionType)
      this.selectedExcludedDay = 1
    },
    selectedRuleActionChanged (e) {
      this.ruleActionProvider = e.target.options[e.target.selectedIndex].dataset.provider
      if (typeof this.$options.components[this.ruleActionProvider] === 'undefined') {
        alert('The rule action is under development.');
      }
      this.ruleActionData = this.rule && this.rule.rule_action_id && this.rule.rule_action_data && this.rule.rule_action_id == this.selectedRuleAction ? JSON.parse(this.rule.rule_action_data) : {}
    },
    saveRule () {
      this.postData = {
        ruleName: this.ruleName,
        ruleAction: this.selectedRuleAction,
        ruleGroup: this.selectedRuleGroup,
        dataFrom: this.selectedDataFrom,
        excludedDay: this.selectedExcludedDay,
        ruleConditions: this.ruleConditionData,
        ruleIntervalAmount: this.ruleIntervalAmount,
        ruleIntervalUnit: this.ruleIntervalUnit,
        ruleRunType: this.ruleRunType,
        ruleWidgetIncluded: this.selectedWidgetIncluded,
        ruleWidget: this.ruleWidget,
        ruleActionSubmitData: this.ruleActionData
      }

      let url = '/rules';

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

</style>