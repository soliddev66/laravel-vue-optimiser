<template>
  <div class="container-fluid">
    <component :is="selectedProvider" :campaign="campaign" :adGroupId="adGroupId" />
  </div>
</template>

<script>
import {
  yahoo,
} from './ad-vendors/ad-creators'

export default {
  props: {
    campaign: {
      type: Object,
      default: null
    },
    adGroupId: {
      type: String,
      default: null
    },
  },

  components: {
    yahoo,
  },

  computed: {},
  mounted() {
    console.log('Component mounted.')
  },
  watch: {

  },
  data() {
    return {
      selectedProvider: this.campaign.provider_slug
    }
  },
  methods: {

  }
}
</script>