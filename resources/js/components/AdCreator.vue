<template>
  <div class="container-fluid">
    <component :is="selectedProvider" :campaign="campaign" :adGroupId="adGroupId" :ad="ad" />
  </div>
</template>

<script>
import {
  yahoo,
  outbrain,
  twitter,
  taboola,
  yahoojp
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
    ad: {
      type: Object,
      default: null
    }
  },

  components: {
    yahoo,
    outbrain,
    twitter,
    taboola,
    yahoojp
  },

  computed: {},
  mounted() {
  },
  watch: {

  },
  data() {
    return {
      selectedProvider: this.campaign.provider_slug
    }
  },
  methods: {
    //
  }
}
</script>
