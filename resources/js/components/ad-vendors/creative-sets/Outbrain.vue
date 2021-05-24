<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      Outbrain
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
    preparingData() {
      console.log('Load data')
    }
  }
}
</script>
