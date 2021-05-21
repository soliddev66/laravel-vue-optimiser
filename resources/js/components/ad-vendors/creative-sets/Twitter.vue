<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      Twitter
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
    //
  },
  data() {
    return {
      isLoading: false,
      fullPage: true
    }
  },
  methods: {

  }
}
</script>
