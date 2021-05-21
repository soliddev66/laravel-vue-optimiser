<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="card-footer">
            <div class="d-flex justify-content-between">
              <button type="button" class="btn btn-primary" @click.prevent="backVendor">Back</button>
              <button type="button" class="btn btn-primary" @click.prevent="submitVendor" :disabled="!submitState">Next</button>
            </div>
          </div>
    </div>
  </section>
</template>

<script>
import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    vendor: {
      type: Object,
      default: null
    }
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    submitState() {
      return true
    }
  },
  mounted() {
    console.log('Component mounted.')
  },
  watch: {
    //
  },
  data() {
    Object.assign(this.vendor, {

    })

    return {
      isLoading: false,
      fullPage: true,
    }
  },
  methods: {
    backVendor() {
      this.$emit('backVendor', this.vendor)
    },

    submitVendor() {
      this.$emit('submitVendor', this.vendor)
    },
  }
}
</script>
