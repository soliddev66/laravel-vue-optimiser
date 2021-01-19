<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Yahoo Japan Ad</h2>
          </div>
          <div class="card-body">
            <form class="form-horizontal">
              <h2 class="pb-2">General information</h2>
              <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in contents" :key="index">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="form-group row">
                      <label for="title" class="col-sm-4 control-label mt-2">Headline</label>
                      <div class="col-sm-8">
                        <div class="row mb-2" v-for="(headline, indexHeadline) in content.headlines" :key="indexHeadline">
                          <div class="col-sm-8">
                            <input type="text" name="headline" placeholder="Enter a headline" class="form-control" v-model="headline.headline" />
                          </div>
                          <div class="col-sm-4">
                            <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexHeadline)" v-if="indexHeadline > 0"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexHeadline + 1 == content.headlines.length"><i class="fa fa-plus"></i></button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="brand_name" class="col-sm-4 control-label mt-2">Principal</label>
                      <div class="col-sm-8">
                        <input type="text" name="principal" placeholder="Principal" class="form-control" v-model="content.principal" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="description" class="col-sm-4 control-label mt-2">Description</label>
                      <div class="col-sm-8">
                        <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description"></textarea>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                      <div class="col-sm-8 text-center">
                        <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="content.displayUrl" />
                        <small class="text-danger" v-if="content.displayUrl && !validURL(content.displayUrl)">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                      <div class="col-sm-8 text-center">
                        <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" />
                        <small class="text-danger" v-if="content.targetUrl && !validURL(content.targetUrl)">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="thumbnail_url" class="col-sm-4 control-label mt-2">Images</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Images" class="form-control" disabled="disabled" v-model="content.imagePath" />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal', index)">Choose Files</button>
                      </div>
                      <div class="col-sm-8 offset-sm-4">
                        <small class="text-danger" v-for="(image, indexImage) in content.images" :key="indexImage">
                          <span class="d-inline-block" v-if="image.image && !image.state">Image {{ image.image }} is invalid. You might need an 1200 x 628 image.</span>
                        </small>
                      </div>
                    </div>
                  </div>
                  <div class="col-sm-5">
                    <h1>Preview</h1>
                    <div class="row mb-2" v-for="(preview, indexPreview) in content.adPreviews" :key="indexPreview">
                      <div class="col" v-html="preview.data"></div>
                    </div>
                  </div>
                </div>
                <div class="row" v-if="index > 0">
                  <div class="col text-right">
                    <button class="btn btn-warning btn-sm" @click.prevent="removeContent(index)">Remove</button>
                  </div>
                </div>
              </fieldset>
              <button class="btn btn-primary btn-sm" @click.prevent="addContent()">Add New</button>
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
    adGroupId: {
      type: String,
      default: null
    },
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    submitState() {
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].principal || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        for (let j = 0; j < this.contents[i].headlines.length; j++) {
          if (!this.contents[i].headlines[j].headline) {
            return false
          }
        }

        if (this.contents[i].images.length == 0) {
          return false
        }

        for (let j = 0; j < this.contents[i].images.length; j++) {
          if (!this.contents[i].images[j].image || !this.validURL(this.contents[i].images[j].image) || !this.contents[i].images[j].state) {
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
        this.contents[this.fileSelectorIndex].images = [];
        let paths = []
        for (let i = 0; i < values.length; i++) {
          this.contents[this.fileSelectorIndex].images.push({
            image: values[i].path,
            state: this.validDimensions(values[i].dimensions, 1200, 628),
            existing: false
          })
          paths.push(values[i].path)
        }
        this.contents[this.fileSelectorIndex].imagePath = paths.join(';')
      }
      vm.$modal.hide('imageModal')
    });
  },
  watch: {

  },
  data() {
    let contents = [{
        id: '',
        headlines: [{
          headline: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        principal: '',
        images: [{
          image: '',
          existing: false
        }],
        imagePath: '',
        adPreviews: []
      }];

    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'yahoojp',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      postData: {},
      contents: contents,
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
    validDimensions(dimensions, width, height) {
      var dimensions = dimensions.split('x')

      if (dimensions.length == 2) {
        if (dimensions[0].trim() == width && dimensions[1].trim() == height) {
          return true
        }
      }

      return false
    },
    addContent() {
      this.contents.push({
        id: '',
        headlines: [{
          headline: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        principal: '',
        images: [{
          image: '',
          existing: false
        }],
        imagePath: '',
        adPreviews: []
      })
    },
    removeContent(index) {
      this.contents.splice(index, 1);
    },
    addTitle(index) {
      this.contents[index].headlines.push({
        headline: '',
        existing: false
      })
    },
    removeTitle(index, indexHeadline) {
      this.contents[index].headlines.splice(indexHeadline, 1)
    },
    submit() {
      this.isLoading = true
      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.campaign.advertiser_id,
        contents: this.contents
      }

      axios.post(`/campaigns/${this.campaign.id}/ad-groups/${this.adGroupId}/ads/store-ad`, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          let message = 'Save successfully!';

          if (response.data.creatorError) {
            message += ' ' + response.data.creatorError;
          }
          this.$dialog.alert(message).then(function(dialog) {
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