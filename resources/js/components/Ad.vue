<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="true"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-body">
            <div class="row">
              <div class="col-6">
                <p>ID: <strong>{{ ad.id }}</strong></p>
                <p>Title: <strong>{{ ad.title }}</strong></p>
                <p>Description: <strong>{{ ad.description }}</strong></p>
                <p>Status: <strong>{{ ad.status }}</strong></p>
                <p>Advertiser ID: <strong>{{ ad.advertiserId }}</strong></p>
                <p>Campaign ID: <strong>{{ ad.campaignId }}</strong></p>
                <p>Ad Group ID: <strong>{{ ad.adGroupId }}</strong></p>
                <p>Display URL: <strong>{{ ad.displayUrl }}</strong></p>
                <p>Landing URL: <strong>{{ ad.landingUrl }}</strong></p>
                <p>Sponsored By: <strong>{{ ad.sponsoredBy }}</strong></p>
              </div>
              <div class="col-6">
                <h3>Preview</h3>
                <div v-html="previewData"></div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import _ from 'lodash'
import Loading from 'vue-loading-overlay'

export default {
  props: {
    ad: {
      type: Object,
      default: null
    }
  },
  components: {
    Loading
  },
  mounted() {
    console.log('Component mounted.')
    this.loadPreview()
  },
  data() {
    return {
      isLoading: false,
      previewData: ''
    }
  },
  methods: {
    loadPreview() {
      this.isLoading = true
      axios.post(`/general/preview?provider=yahoo&account=${this.ad.open_id}`, {
        title: this.ad.title,
        displayUrl: this.ad.displayUrl,
        landingUrl: this.ad.landingUrl,
        description: this.ad.description,
        sponsoredBy: this.ad.sponsoredBy,
        imageUrlHQ: this.ad.imageUrlHQ,
        imageUrl: this.ad.imageUrl
      }).then(response => {
        this.previewData = response.data.replace('height="800"', 'height="450"').replace('width="400"', 'width="100%"')
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
</style>
