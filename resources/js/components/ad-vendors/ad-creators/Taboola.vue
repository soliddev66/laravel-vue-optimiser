<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Taboola Ad</h2>
          </div>
          <div class="card-body">
            <form class="form-horizontal">
              <h2 class="pb-2">General information</h2>
              <fieldset class="mb-3 p-3 rounded border" v-for="(campaignItem, index) in campaignItems" :key="index">
                <div class="form-group row">
                  <label for="url" class="col-sm-2 control-label mt-2">Url</label>
                  <div class="col-sm-8">
                    <input type="text" name="url" placeholder="Enter a url" class="form-control" v-model="campaignItem.url" />
                    <small class="text-danger" v-if="campaignItem.url && !validURL(campaignItem.url)">URL is invalid. You might need http/https at the beginning.</small>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="url" class="col-sm-2 control-label mt-2">Title</label>
                  <div class="col-sm-8">
                    <div class="row mb-2" v-for="(title, indexTitle) in campaignItem.titles" :key="indexTitle">
                      <div class="col-sm-8">
                        <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" />
                      </div>
                      <div class="col-sm-4">
                        <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle)" v-if="indexTitle > 0"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == campaignItem.titles.length"><i class="fa fa-plus"></i></button>
                      </div>
                    </div>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="description" class="col-sm-2 control-label mt-2">Description</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" rows="3" placeholder="Enter description" v-model="campaignItem.description"></textarea>
                  </div>
                </div>

                <div class="form-group row">
                  <label for="thumbnail_url" class="col-sm-4 control-label mt-2">Thumbnail Images</label>
                  <div class="col-sm-8">
                    <input type="text" name="thumbnail_url" placeholder="Thumbnail Images" class="form-control" disabled="disabled" v-model="campaignItem.imagePath" />
                      <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal', index)">Choose Files</button>
                  </div>
                </div>

                <div class="row" v-if="index > 0 && action == 'create'">
                    <div class="col text-right">
                      <button class="btn btn-warning btn-sm" @click.prevent="removeCampaignItem(index)">Remove</button>
                    </div>
                  </div>
              </fieldset>
              <button class="btn btn-primary btn-sm" @click.prevent="addCampaignItem()" v-if="action == 'create'">Add New</button>
            </form>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-primary" @click.prevent="submit" :disabled="!submitState">
              <span v-if="action == 'create'">Save</span>
              <span v-if="action == 'edit'">Update</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <modal width="60%" height="80%" name="imageModal">
      <file-manager v-bind:settings="settings" :props="{
          upload: true,
          viewType: 'grid',
          selectionType: 'multiple'
      }"></file-manager>
    </modal>
  </section>
</template>

<script>
import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import 'vue-loading-overlay/dist/vue-loading.css'

let adPreviewCancels = []

export default {
  props: {
    campaign: {
      type: Object,
      default: null
    },
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    submitState() {
      for (let i = 0; i < this.campaignItems.length; i++) {
        if (!this.campaignItems[i].url) {
          return false
        }
      }

      return true
    }
  },
  mounted() {
    console.log('Component mounted.')
    let vm = this
    this.$root.$on('fm-selected-items', (values) => {
      if (this.openingFileSelector === 'imageModal') {
        this.campaignItems[this.fileSelectorIndex].images = [];
        let paths = []
        for (let i = 0; i < values.length; i++) {
          this.campaignItems[this.fileSelectorIndex].images.push({
            image: process.env.MIX_APP_URL + '/storage/images/' + values[i].path,
            existing: false
          })
          paths.push(values[i].path)
        }
        this.campaignItems[this.fileSelectorIndex].imagePath = paths.join(';')
      }
      vm.$modal.hide('imageModal')
    });
  },
  watch: {

  },
  data() {
    let campaignItems = [{
      url: '',
      titles: [{
        title: '',
        existing: false
      }],
      description: '',
      images: [{
        image: '',
        existing: false
      }],
      imagePath: ''
    }]

    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'taboola',
      action: 'create',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      postData: {},
      adIds: [],
      campaignItems: campaignItems,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      }
    }
  },
  methods: {
    openChooseFile(name, index) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show('imageModal')
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    addCampaignItem(index) {
      this.campaignItems.push({
      url: '',
      titles: [{
        title: '',
        existing: false
      }],
      description: '',
      images: [{
        image: '',
        existing: false
      }],
      imagePath: ''
    })
    },
    removeCampaignItem(index) {
      this.campaignItems.splice(index, 1)
    },
    addTitle(index) {
      this.campaignItems[index].titles.push({
        title: '',
        existing: false
      })
    },
    removeTitle(index, indexTitle) {
      this.campaignItems[index].titles.splice(indexTitle, 1)
    },

    submit() {
      this.isLoading = true
      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        advertiser: this.campaign.advertiser_id,
        campaignItems: this.campaignItems
      }
      let url = `/campaigns/${this.campaign.id}/ad-groups/ad-group/ads/`;
      url += this.action == 'edit' ? 'update-ad' : 'store-ad'
      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
          this.isLoading = false
        } else {
          let me = this

          this.campaignItems = []

          for (let i = 0; i < response.data.length; i++) {
            this.campaignItems.push({
              id: response.data[i].ad_id,
              url: response.data[i].url,
              titles: [{
                title: response.data[i].name,
                existing: true
              }],
              description: response.data[i].description,
              images: [{
                image: response.data[i].image,
                existing: true
              }],
              imagePath: response.data[i].image
            })
          }
          let interval = setInterval(function () {
            axios.post('/campaigns/item-status', {
              provider: me.selectedProvider,
              account: me.selectedAccount,
              advertiser: me.campaign.advertiser_id,
              campaignId: me.campaign.campaign_id
            }).then(response => {
              if (response.data.errors) {
                alert(response.data.errors[0])
                me.isLoading = false
              } else if (response.data.status) {
                clearInterval(interval)
                me.action = 'edit'
                me.isLoading = false
              }
            })
          }, 10000)
        }
      }).catch(error => {
        console.log(error)
      })
    }
  }
}
</script>