<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <label class="pt-1 mb-0">VENDOR</label>
          </div>
          <div class="card-body">
            <div class="row form-inline">
              <div class="col-6">
                <select2 v-model="selectedProvider" :options="formatedProviders" :settings="{ templateSelection: formatState, templateResult: formatState, multiple: false, placeholder: 'Select Traffic Source' }" @change="selectedProviderChanged()" :disabled="instance != null" />
              </div>
              <div class="col-6">
                <select class="form-control" v-if="accounts.length" v-model="selectedAccount" :disabled="instance">
                  <option value="">Select Account</option>
                  <option :value="account.open_id" v-for="account in accounts" :key="account.id">{{ account.open_id }}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
        <component :is="selectedProvider" :providers="providers" :instance="instance" :action="action" :selectedProvider="selectedProvider" :selectedAccount="selectedAccount" v-if="selectedAccount" />
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
  yahoo,
  outbrain,
  twitter,
  taboola,
  yahoojp
} from './ad-vendors'

export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    instance: {
      type: Object,
      default: null
    },
    action: {
      type: String,
      default: 'create'
    },
    step: {
      type: Number,
      default: 1
    }
  },
  components: {
    Loading,
    Select2,
    yahoo,
    outbrain,
    twitter,
    taboola,
    yahoojp
  },
  computed: {
    formatedProviders() {
      return this.providers.map(provider => {
        provider.id = provider.slug
        provider.text = provider.label
        return provider
      })
    }
  },
  mounted() {
    console.log('Component mounted.')
    this.getAccounts()
  },
  watch: {

  },
  data() {
    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: this.instance ? this.instance.provider : 'yahoo',
      accounts: [],
      selectedAccount: this.instance ? this.instance.open_id : '',
    }
  },
  methods: {
    formatState(state) {
      if (!state.id) {
        return state.text;
      }
      var $state = $(
        '<span><img src="' + state.icon + '" width="20px" height="20px" /> ' + state.text + '</span>'
      );
      return $state;
    },
    selectedProviderChanged() {
      this.selectedAccount = ''
      this.getAccounts()
    },
    getAccounts() {
      this.accounts = []
      this.isLoading = true
      axios.get(`/account/accounts?provider=${this.selectedProvider}`).then(response => {
        this.accounts = response.data
        this.selectedAccount = this.accounts[0].open_id
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>
