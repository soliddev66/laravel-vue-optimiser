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

                <div class="form-group row" v-if="action == 'edit'">
                  <label for="url" class="col-sm-2 control-label mt-2">Title</label>
                  <div class="col-sm-8">
                    <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="campaignItem.title" />
                  </div>
                </div>

                <div class="form-group row" v-if="action == 'edit'">
                  <label for="description" class="col-sm-2 control-label mt-2">Description</label>
                  <div class="col-sm-8">
                    <textarea class="form-control" rows="3" placeholder="Enter description" v-model="campaignItem.description"></textarea>
                  </div>
                </div>

                <div class="form-group row" v-if="action == 'edit'">
                  <label for="thumbnail_url" class="col-sm-2 control-label mt-2"></label>
                  <div class="col-sm-8">
                    <input type="text" name="thumbnail_url" placeholder="Enter a image url" class="form-control" v-model="campaignItem.thumbnail_url" />
                    <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageThumbnailUrl', index)">Choose File</button>
                  </div>
                  <div class="col-sm-8 offset-sm-2">
                    <small class="text-danger" v-if="campaignItem.thumbnail_url && !validURL(campaignItem.thumbnail_url)">URL is invalid. You might need http/https at the beginning.</small>
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
          selectionType: 'single'
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
    this.$root.$on('fm-selected-items', (value) => {
      if (value.length == 0)
        return

      const selectedFilePath = value[0].path
      if (this.openingFileSelector === 'imageThumbnailUrl') {
        this.campaignItems[this.fileSelectorIndex].thumbnail_url = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
      }
      vm.$modal.hide('imageModal')
    });
  },
  watch: {

  },
  data() {
    let campaignItems = [{
      url: ''
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
    openChooseFile(name, index, indexImage) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show('imageModal')
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    addCampaignItem(index) {
      this.campaignItems.push({ url: '' })
    },
    removeCampaignItem(index) {
      this.campaignItems[index].splice(index, 1)
    },

    submit() {
      this.isLoading = true
      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.campaign.advertiser_id,
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

          if (me.action == 'edit') {
            me.isLoading = false
            this.$dialog.alert('Save successfully!').then(function(dialog) {
              window.location = '/campaigns';
            });

            return
          }

          this.campaignItems = response.data
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