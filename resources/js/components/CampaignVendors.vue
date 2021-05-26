<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>

    <div class="row justify-content-center">
      <div class="col vendors mt-3" v-if="currentStep == 1">
        <div class="row vendor" v-for="vendor in vendors" :key="vendor.id">
          <div class="col">
            <div class="card" v-bind:class="{active: vendor.selected}" @click="vendorClick($event, vendor)">
              <div class="card-body">
                <img :src="vendor.icon" alt="" width="40">
                <span class="pl-3">{{ vendor.label }}</span>

                <div class="row mt-3" v-if="vendor.selected">
                  <div class="col">
                    <select class="form-control" v-model="vendor.selectedAccount">
                      <option value="">Select Account</option>
                      <option :value="account.open_id" v-for="account in vendor.accounts" :key="account.id">{{ account.open_id }}</option>
                    </select>
                  </div>
                </div>

                <div class="row mt-2" v-if="vendor.selected">
                  <div class="col">
                    <select class="form-control" v-model="vendor.selectedAdvertiser">
                      <option value="">Select Advertiser</option>
                      <option :value="advertiser.id" v-for="advertiser in vendor.advertisers" :key="advertiser.id">{{ advertiser.id }} - {{ advertiser.advertiserName || advertiser.name }}</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col d-none" :class="{ 'd-block': currentStep != 1 }">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">General Information</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Vendors</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 4 }">Ad Content</label>
            <i class="fas fa-arrow-right"></i>
          </div>
          <div class="card-body">
            <div v-if="currentStep == 2">
              <div class="form-group row">
                <label for="name" class="col-sm-2 control-label mt-2">Campaign Name</label>
                <div class="col-sm-8">
                  <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="campaignName" />
                </div>
              </div>
            </div>

            <div class="d-none" :class="{ 'd-block': currentStep == 3 }">
              <div v-for="vendor in vendors" :key="vendor.id">
                <div class="d-none" :class="{ 'd-block': vendor.selected && currentVendor.slug == vendor.slug }" v-if="vendor.selected">
                  <component :is="vendor.slug" :vendor="vendor" :ref="vendor.slug" />
                </div>
              </div>
            </div>
          </div>
          <div class="card-footer d-flex justify-content-end" v-if="currentStep != 1">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 5 && currentStep > 1 && currentStep != 3">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>

            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep === 3">
              <div v-for="vendor in vendors" :key="vendor.id">
                <button type="button" class="btn btn-primary" v-if="vendor.selected && currentVendor.slug == vendor.slug" @click.prevent="backVendor(vendor)">Back</button>
              </div>
            </div>

            <div class="d-flex justify-content-end" v-if="currentStep == 2">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep2">Next</button>
            </div>

            <div v-if="currentStep === 3">
              <div v-for="vendor in vendors" :key="vendor.id">
                <div class="d-flex justify-content-end" v-if="vendor.selected && currentVendor.slug == vendor.slug">
                  <button type="button" class="btn btn-primary" @click.prevent="submitVendor(vendor)" :disabled="!$refs[vendor.slug][0].vendorState">Next</button>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end" v-if="currentStep == 4">
              <button type="button" class="btn btn-primary" @click.prevent="submitStep4">Finish</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end mb-5" v-if="currentStep == 1">
      <button type="button" class="btn btn-vendor mr-5" :disabled="!submitStep1State" @click="submitStep1">
        Next <i class="fas fa-long-arrow-alt-right"></i>
      </button>
    </div>
  </div>
</template>

<script>
import {
  yahoo,
  outbrain,
  yahoojp,
  twitter,
  taboola
} from './ad-vendors/creative-sets'

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
    Select2,
    yahoo,
    outbrain,
    yahoojp,
    twitter,
    taboola
  },
  computed: {
    submitStep1State() {
      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected) {
          return true
        }
      }

      return false
    }
  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {

  },
  data() {
    let vendors = []

    for (let i = 0; i < this.providers.length; i++) {
      let vendor = {
        id: this.providers[i].id,
        slug: this.providers[i].slug,
        label: this.providers[i].label,
        icon: this.providers[i].icon,
        accounts: [],
        advertisers: [],
        selectedAccount: '',
        selectedAdvertiser: '',
        selected: false,
        loaded: false
      }

      if (this.providers[i].slug == 'outbrain') {
        Object.assign(vendor, {
          campaignStartDate: this.$moment().format('YYYY-MM-DD'),
          campaignObjective: 'Awareness',
          campaignBudget: 20
        })
      } else if (this.providers[i].slug == 'yahoojp') {
        Object.assign(vendor, {
          campaignStartDate: this.$moment().format('YYYY-MM-DD')
        })
      }

      vendors.push(vendor)
    }

    return {
      isLoading: false,
      fullPage: true,
      currentStep: this.step,
      currentVendor: vendors[0],
      vendors: vendors,
      campaignName: ''
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

          this.getAdvertisers(vendor)
        })
      } else {
        vendor.selected = !vendor.selected
      }

      vendor.loaded = false
    },

    submitStep1() {
      this.currentStep = 2
    },

    submitStep2() {
      this.currentStep = 3

      for (let i = 0; i < this.vendors.length; i++) {
        if (this.vendors[i].selected) {
          this.currentVendor = this.vendors[i]
          break
        }
      }

      if (!this.currentVendor.loaded) {
        this.$refs[this.currentVendor.slug][0].preparingData();
        this.currentVendor.loaded = true;
      }
    },

    backVendor(vendor) {
      let found = false

      for (let i = this.vendors.length - 1; i >= 0; i--) {
        if (this.vendors[i].slug == vendor.slug) {
          found = true
          continue
        }

        if (found && this.vendors[i].selected) {
          this.currentVendor = this.vendors[i]
          return
        }
      }

      this.currentStep = 2
    },

    submitVendor(vendor) {
      let found = false

      for (let i = 0; i < this.vendors.length; i++) {

        if (this.vendors[i].slug == vendor.slug) {
          found = true
          continue
        }

        if (found && this.vendors[i].selected) {
          this.currentVendor = this.vendors[i]

          if (!this.currentVendor.loaded) {
            this.$refs[this.currentVendor.slug][0].preparingData();
            this.currentVendor.loaded = true;
          }

          return
        }
      }

      if (!this.currentVendor.loaded) {
        this.$refs[this.currentVendor.slug][0].preparingData();
        this.currentVendor.loaded = true;
      }

      this.currentStep = 4
    },

    submitStep4() {
      console.log(this.vendors)
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
    },

    getAdvertisers(vendor) {
      this.isLoading = true
      return axios.get(`/account/advertisers?provider=${vendor.slug}&account=${encodeURIComponent(vendor.selectedAccount)}`).then(response => {
        vendor.advertisers = response.data
        vendor.selectedAdvertiser = vendor.advertisers[0].id
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
  height: 180px;
}

.vendor .card.active, .vendor .card:hover {
  background: #607cef;
  color: #fff;

}
</style>