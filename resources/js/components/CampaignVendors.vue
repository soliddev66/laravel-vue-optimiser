<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>

    <div class="row justify-content-center">
      <div class="col vendors mt-3">
        <div class="row vendor" v-for="vendor in vendors" :key="vendor.id">
          <div class="col">
            <div class="card" v-bind:class="{active: vendor.selected}" @click="vendorClick($event, vendor)">
              <div class="card-body">
                <img :src="vendor.icon" alt="" width="40">
                <span class="pl-3">{{ vendor.label }}</span>

                <div class="row mt-3" v-if="vendor.selected">
                  <div class="col">
                    <select class="form-control" v-if="vendor.accounts.length" v-model="vendor.selectedAccount">
                      <option value="">Select Account</option>
                      <option :value="account.open_id" v-for="account in vendor.accounts" :key="account.id">{{ account.open_id }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end mb-5">
      <button type="button" class="btn btn-vendor mr-5" :disabled="!submitStep1State">Next <i class="fas fa-long-arrow-alt-right"></i></button>
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
    providers: {
      type: Array,
      default: []
    },
    step: {
      type: Number,
      default: 1
    }
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    submitStep1State() {
      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected) {
          return true
        }
      }

      return false
    },
  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {

  },
  data() {
    let vendors = []

    for (let i = 0; i < this.providers.length; i++) {
      vendors.push({
        id: this.providers[i].id,
        slug: this.providers[i].slug,
        label: this.providers[i].label,
        icon: this.providers[i].icon,
        accounts: [],
        selectedAccount: null,
        selected: false
      })
    }

    return {
      isLoading: false,
      fullPage: true,
      vendors: vendors
    }
  },
  methods: {
    vendorClick(event, vendor) {
      if (event.target.className == 'form-control') {
        return
      }

      if (vendor.accounts.length == 0) {
        this.getAccounts(vendor).then(() => {
          vendor.selected = !vendor.selected
        })
      } else {
        vendor.selected = !vendor.selected
      }
    },

    getAccounts(vendor) {
      this.isLoading = true
      return axios.get(`/account/accounts?provider=${vendor.slug}`).then(response => {
        vendor.accounts = response.data
        vendor.selectedAccount = vendor.accounts[0].open_id
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>

<style>
.btn-vendor {
  color: #4969ed;
  min-height: 50px;
  border: none;
}

.btn-vendor:hover {
  color: #344eb4;
}

.vendors {
  max-width: 350px;
  cursor: pointer;
}

.vendor span {
  font-size: 1.3em;
}

.vendor .card {
  border: none;
  transition: all 0.3s linear;
  height: 80px;
  overflow: hidden;
}

.vendor .card.active {
  height: 135px;
}

.vendor .card.active, .vendor .card:hover {
  background: #607cef;
  color: #fff;

}
</style>