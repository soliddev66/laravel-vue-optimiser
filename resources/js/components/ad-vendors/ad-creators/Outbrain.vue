<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Outbrain Ad</h2>
          </div>
          <div class="card-body">
            <form class="form-horizontal">
              <h2 class="pb-2">General information</h2>
              <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in ads" :key="index">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="form-group row">
                      <label for="title" class="col-sm-4 control-label mt-2">Title</label>
                      <div class="col-sm-8">
                        <div class="row mb-2" v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                          <div class="col-sm-8">
                            <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" />
                          </div>
                          <div class="col-sm-4">
                            <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle)" v-if="indexTitle > 0"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.titles.length"><i class="fa fa-plus"></i></button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name</label>
                      <div class="col-sm-8">
                        <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="content.brandname" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="cpc" class="col-sm-4 control-label mt-2">CPC</label>
                      <div class="col-sm-8">
                        <input type="number" name="cpc" placeholder="Enter cpc" class="form-control" v-model="content.cpc" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                      <div class="col-sm-8">
                        <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" />
                        <small class="text-danger" v-if="content.targetUrl && !validURL(content.targetUrl)">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="image_url" class="col-sm-4 control-label mt-2">Image URL</label>
                      <div class="col-sm-8">
                        <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="content.imageUrl" disabled="true" />
                      </div>
                      <div class="col-sm-8 offset-sm-4">
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal', index)">Choose File</button>
                      </div>
                      <div class="col-sm-8 offset-sm-4">
                        <small class="text-danger" v-for="(image, indexImage) in content.images" :key="indexImage">
                          <span class="d-inline-block" v-if="image.url && !validURL(image.url)">URL {{ image.url }} is invalid. You might need http/https at the beginning.</span>
                          <span class="d-inline-block" v-if="image.url && !image.state">Image {{ image.url }} is invalid. You might need an 1200 x 800 image.</span>
                        </small>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <h1>Preview</h1>
                    <section v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                      <section v-for="(image, indexImage) in content.images" :key="indexImage">
                        <div class="row no-gutters mb-2" v-if="image.url">
                          <div class="col-sm-5">
                            <img :src="image.url" class="card-img-top h-100">
                          </div>
                          <div class="col-sm-7">
                            <div class="card-body">
                              <h3 class="card-title">{{ title.title }}</h3>
                              <h6 class="card-text mt-5"><i>{{ content.brandname }}</i></h6>
                            </div>
                          </div>
                        </div>
                      </section>
                    </section>
                  </div>
                </div>
                <div class="row" v-if="index > 0">
                  <div class="col text-right">
                    <button class="btn btn-warning btn-sm" @click.prevent="removeAd(index)">Remove</button>
                  </div>
                </div>
              </fieldset>
              <button class="btn btn-primary btn-sm d-none" @click.prevent="addAd()">Add New</button>
            </form>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-primary" @click.prevent="submit" :disabled="!submitState">Save</button>
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
      for (let i = 0; i < this.ads.length; i++) {
        if (!this.ads[i].brandname || !this.ads[i].cpc || this.ads[i].cpc <= 0 || !this.ads[i].targetUrl || !this.validURL(this.ads[i].targetUrl)) {
          return false
        }

        for (let j = 0; j < this.ads[i].titles.length; j++) {
          if (!this.ads[i].titles[j].title) {
            return false
          }
        }

        if (this.ads[i].images.length == 0) {
          return false
        }

        for (let j = 0; j < this.ads[i].images.length; j++) {
          if (!this.ads[i].images[j].url || !this.validURL(this.ads[i].images[j].url) || !this.ads[i].images[j].state) {
            return false
          }
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
        this.ads[this.fileSelectorIndex].images = []
        this.ads[this.fileSelectorIndex].imageUrl = ''
        let paths = []
        for (var i = 0; i < values.length; i++) {
          this.ads[this.fileSelectorIndex].images[i] = {
            url: process.env.MIX_APP_URL + '/storage/images/' + values[i].path,
            state: this.validDimensions(values[i].width, values[i].height, 1200, 800),
            existing: false
          };
          paths.push(values[i].path)
        }
        this.ads[this.fileSelectorIndex].imageUrl += paths.join(';')
      }
      vm.$modal.hide(this.openingFileSelector)
    });
  },
  watch: {

  },
  data() {
    let ads = [{
      adId: '',
      titles: [{
        title: '',
        existing: false
      }],
      targetUrl: '',
      cpc: '',
      brandname: '',
      imageUrl: '',
      images: []
    }]

    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'outbrain',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      postData: {},
      ads: ads,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      fileSelectorIndexImage: 0,
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
      this.fileSelectorIndexImage = indexImage
      this.$modal.show('imageModal')
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validDimensions(fileWidth, fileHeight, width, height) {
      return fileWidth == width && fileHeight == height
    },
    addAd() {
      this.ads.push({
        adId: '',
        titles: [{
          title: '',
          existing: false
        }],
        targetUrl: '',
        cpc: '',
        brandname: '',
        imageUrl: '',
        images: []
      })
    },
    removeAd(index) {
      this.ads.splice(index, 1)
    },
    addTitle(index) {
      this.ads[index].titles.push({
        title: '',
        existing: false
      })
    },
    removeTitle(index, indexTitle) {
      this.ads[index].titles.splice(indexTitle, 1)
    },

    submit() {
      this.isLoading = true
      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.campaign.advertiser_id,
        ads: this.ads
      }

      axios.post(`/campaigns/${this.campaign.id}/ad-groups/ad-group/ads/store-ad`, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = '/campaigns';
          });
        }
      }).catch(error => {
        console.log(error)
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>
