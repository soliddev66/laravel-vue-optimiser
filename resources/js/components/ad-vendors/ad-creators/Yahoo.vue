<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Yahoo Ad</h2>
          </div>
          <div class="card-body">
            <form class="form-horizontal">
              <h2 class="pb-2">General information</h2>
              <fieldset class="mb-3 p-3 rounded border" v-for="(content, index) in contents" :key="index">
                <div class="row">
                  <div class="col-sm-7">
                    <div class="form-group row">
                      <label for="title" class="col-sm-4 control-label mt-2">Title</label>
                      <div class="col-sm-8">
                        <div class="row mb-2" v-for="(title, indexTitle) in content.titles" :key="indexTitle">
                          <div class="col-sm-8">
                            <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" v-on:blur="loadPreviewEvent($event, index)" />
                          </div>
                          <div class="col-sm-4">
                            <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle); loadPreviewEvent($event, index)" v-if="indexTitle > 0"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.titles.length"><i class="fa fa-plus"></i></button>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name</label>
                      <div class="col-sm-8">
                        <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="content.brandname" v-on:blur="loadPreviewEvent($event, index)" />
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="description" class="col-sm-4 control-label mt-2">Description</label>
                      <div class="col-sm-8">
                        <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description" v-on:blur="loadPreviewEvent($event, index)"></textarea>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                      <div class="col-sm-8 text-center">
                        <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="content.displayUrl" v-on:blur="loadPreviewEvent($event, index)" />
                        <small class="text-danger" v-if="content.displayUrl && !validURL(content.displayUrl)">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                      <div class="col-sm-8 text-center">
                        <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="content.targetUrl" v-on:blur="loadPreviewEvent($event, index)" />
                        <small class="text-danger" v-if="content.targetUrl && !validURL(content.targetUrl)">URL is invalid. You might need http/https at the beginning.</small>
                      </div>
                    </div>
                    <fieldset class="mb-3 p-3 rounded border" v-for="(image, indexImage) in content.images" :key="indexImage">
                      <div class="form-group row">
                        <label for="image_hq_url" class="col-sm-4 control-label mt-2" v-html="'Image HQ URL <br> (1200 x 627 px)'"></label>
                        <div class="col-sm-8">
                          <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrlHQ" v-on:blur="loadPreviewEvent($event, index); validImageHQSizeEvent($event, index)" />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQUrl', index, indexImage)">Choose File</button>
                        </div>
                        <div class="col-sm-8 offset-sm-4">
                          <small class="text-danger" v-if="image.imageUrlHQ && !validURL(image.imageUrlHQ)">URL is invalid. You might need http/https at the beginning.</small>
                          <small class="text-danger" v-if="!image.imageUrlHQState">Image is invalid. You might need an 1200x627 image.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="image_url" class="col-sm-4 control-label mt-2" v-html="'Image URL <br> (627 x 627 px)'"></label>
                        <div class="col-sm-8">
                          <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrl" v-on:blur="loadPreviewEvent($event, index); validImageSizeEvent($event, index)" />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageUrl', index, indexImage)">Choose File</button>
                        </div>
                        <div class="col-sm-8 offset-sm-4">
                          <small class="text-danger" v-if="image.imageUrl && !validURL(image.imageUrl)">URL is invalid. You might need http/https at the beginning.</small>
                          <small class="text-danger" v-if="!image.imageUrlState">Image is invalid. You might need an 627x627 image.</small>
                        </div>
                      </div>
                      <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeImage(index, indexImage); loadPreviewEvent($event, index)" v-if="indexImage > 0">Remove Image</button>
                    </fieldset>
                    <button class="btn btn-primary btn-sm" @click.prevent="addImage(index)">Add Image</button>
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
        if (!this.contents[i].brandname || !this.contents[i].description || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        for (let j = 0; j < this.contents[i].titles.length; j++) {
          if (!this.contents[i].titles[j].title) {
            return false
          }
        }

        for (let j = 0; j < this.contents[i].images.length; j++) {
          if (!this.contents[i].images[j].imageUrlHQ || !this.validURL(this.contents[i].images[j].imageUrlHQ) || !this.contents[i].images[j].imageUrl || !this.validURL(this.contents[i].images[j].imageUrl) || !this.contents[i].images[j].imageUrlHQState || !this.contents[i].images[j].imageUrlState) {
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
    this.$root.$on('fm-selected-items', (value) => {
      const selectedFilePath = value[0].path
      if (this.openingFileSelector === 'imageHQUrl') {
        this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlHQ = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
        this.validImageSize(this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlHQ, 1200, 627).then(result => {
          this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlHQState = result
        });
        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'imageUrl') {
        this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
        this.validImageSize(this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrl, 627, 627).then(result => {
          this.contents[this.fileSelectorIndex].images[this.fileSelectorIndexImage].imageUrlState = result
        });
        this.loadPreview(this.fileSelectorIndex)
      }
      vm.$modal.hide('imageModal')
    });
  },
  watch: {

  },
  data() {
    let contents = [{
        id: '',
        titles: [{
          title: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        brandname: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true,
          existing: false
        }],
        adPreviews: []
      }];

    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'yahoo',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      postData: {},
      contents: contents,
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
    validImageSize(imageUrl, width, height) {
      return new Promise((resolve) => {
        var image = new Image();
        image.onload = function() {
          resolve(this.width == width && this.height == height);
        };
        image.src = imageUrl;
      });
    },
    addContent() {
      this.contents.push({
        id: '',
        titles: [{
          title: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        description: '',
        brandname: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true,
          existing: false
        }],
        adPreviews: []
      })
    },
    removeContent(index) {
      this.contents.splice(index, 1);
    },
    addTitle(index) {
      this.contents[index].titles.push({
        title: '',
        existing: false
      })
    },
    removeTitle(index, indexTitle) {
      this.contents[index].titles.splice(indexTitle, 1)
    },
    addImage(index) {
      this.contents[index].images.push({
        imageUrlHQ: '',
        imageUrlHQState: true,
        imageUrl: '',
        imageUrlState: true,
        existing: false
      })
    },
    removeImage(index, indexImage) {
      this.contents[index].images.splice(indexImage, 1)
    },
    loadPreviewEvent(event, index) {
      this.loadPreview(index)
    },
    validImageHQSizeEvent(event, index) {
      this.validImageSize(this.contents[index].imageUrlHQ, 1200, 627).then(result => {
        this.contents[index].imageUrlHQState = result
      });
    },
    validImageSizeEvent(event, index) {
      this.validImageSize(this.contents[index].imageUrl, 627, 627).then(result => {
        this.contents[index].imageUrlState = result
      });
    },
    loadPreview(index, firstLoad = false) {
      if (!firstLoad && adPreviewCancels.length > 0) {
        for (let i = 0; i < adPreviewCancels.length; i++) {
          adPreviewCancels[i]()
        }
      }
      this.isLoading = true
      this.contents[index].adPreviews = [];

      for (let i = 0; i < this.contents[index].titles.length; i++) {
        for (let y = 0; y < this.contents[index].images.length; y++) {
          axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
            title: this.contents[index].titles[i].title,
            displayUrl: this.contents[index].displayUrl,
            landingUrl: this.contents[index].targetUrl,
            description: this.contents[index].description,
            sponsoredBy: this.contents[index].brandname,
            imageUrlHQ: this.contents[index].images[y].imageUrlHQ,
            imageUrl: this.contents[index].images[y].imageUrl,
            campaignLanguage: this.campaignLanguage
          }, {
            cancelToken: new axios.CancelToken(function executor(c) {
              adPreviewCancels.push(c);
            })
          }).then(response => {
            this.contents[index].adPreviews.push({
              data: response.data.replace('height="800"', 'height="450"').replace('width="400"', 'width="100%"')
            })
          }).catch(err => {
            console.log(err)
          }).finally(() => {
            this.isLoading = false
          })
        }
      }
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