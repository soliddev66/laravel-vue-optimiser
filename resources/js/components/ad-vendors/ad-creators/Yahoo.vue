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
                            <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title.title" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.titleSet.id" />
                          </div>
                          <div class="col-sm-4">
                            <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle); loadPreviewEvent($event, index)" v-if="indexTitle > 0" :disabled="content.titleSet.id"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.titles.length" :disabled="content.id || content.titleSet.id"><i class="fa fa-plus"></i></button>
                            <button type="button" class="btn btn-primary" v-if="indexTitle == 0" @click="loadCreativeSet('title', index)"><i class="far fa-folder-open"></i></button>
                          </div>
                        </div>

                        <div class="row" v-if="content.titleSet.id">
                          <div class="col">
                            <span class="selected-set">{{ content.titleSet.name }}<span class="close" @click="removeTitleSet(index)"><i class="fas fa-times"></i></span></span>
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
                        <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.descriptionSet.id"></textarea>
                        <div class="row mt-2">
                          <div class="col">
                            <span v-if="content.descriptionSet.id" class="selected-set">{{ content.descriptionSet.name }}<span class="close" @click="removeDescriptionSet(index)"><i class="fas fa-times"></i></span></span>
                          </div>
                        </div>
                        <button class="btn btn-primary btn-sm mt-2" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('description', index)">Load from Sets</button>
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

                    <div class="form-group row">
                      <label for="ad_type" class="col-sm-4 control-label mt-2">Ad Type</label>
                      <div class="col-sm-8">
                        <div class="btn-group btn-group-toggle">
                          <label class="btn bg-olive" :class="{ active: content.adType === 'IMAGE' }">
                            <input type="radio" name="ad_type" autocomplete="off" value="IMAGE" v-model="content.adType"> IMAGE
                          </label>
                          <label class="btn bg-olive" :class="{ active: content.adType === 'VIDEO' }">
                            <input type="radio" name="ad_type" autocomplete="off" value="VIDEO" v-model="content.adType"> VIDEO
                          </label>
                        </div>
                      </div>
                    </div>

                    <div v-if="content.adType == 'IMAGE'">
                      <fieldset class="mb-3 p-3 rounded border" v-for="(image, indexImage) in content.images" :key="indexImage">
                        <div class="form-group row">
                          <label for="image_hq_url" class="col-sm-4 control-label mt-2" v-html="'Image HQ URL <br> (1200 x 627 px)'"></label>
                          <div class="col-sm-8">
                            <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrlHQ" v-on:blur="loadPreviewEvent($event, index); validImageHQSizeEvent($event, index)" :disabled="content.imageSet.id" />
                            <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQUrl', index, indexImage)" :disabled="content.imageSet.id">Choose File</button>
                          </div>
                          <div class="col-sm-8 offset-sm-4">
                            <small class="text-danger" v-if="image.imageUrlHQ && !validURL(image.imageUrlHQ)">URL is invalid. You might need http/https at the beginning.</small>
                            <small class="text-danger" v-if="!image.imageUrlHQState">Image is invalid. You might need an 1200x627 image.</small>
                          </div>
                        </div>
                        <div class="form-group row">
                          <label for="image_url" class="col-sm-4 control-label mt-2" v-html="'Image URL <br> (627 x 627 px)'"></label>
                          <div class="col-sm-8">
                            <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="image.imageUrl" v-on:blur="loadPreviewEvent($event, index); validImageSizeEvent($event, index)" :disabled="content.imageSet.id" />
                            <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageUrl', index, indexImage)" :disabled="content.imageSet.id">Choose File</button>
                          </div>
                          <div class="col-sm-8 offset-sm-4">
                            <small class="text-danger" v-if="image.imageUrl && !validURL(image.imageUrl)">URL is invalid. You might need http/https at the beginning.</small>
                            <small class="text-danger" v-if="!image.imageUrlState">Image is invalid. You might need an 627x627 image.</small>
                          </div>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeImage(index, indexImage); loadPreviewEvent($event, index)" v-if="indexImage > 0">Remove Image</button>
                      </fieldset>
                      <div class="row mt-2 mb-2">
                        <div class="col">
                          <span v-if="content.imageSet.id" class="selected-set">{{ content.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                      <button class="btn btn-primary btn-sm" @click.prevent="addImage(index)" :disabled="content.id || content.imageSet.id">Add Image</button>
                      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                    </div>

                    <div v-if="content.adType == 'VIDEO'">
                      <fieldset class="mb-3 p-3 rounded border" v-for="(video, indexVideo) in content.videos" :key="indexVideo">
                        <div v-if="!['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(campaignObjective)">
                          <div class="form-group row">
                            <label for="image_portrait_url" class="col-sm-4 control-label mt-2" v-html="'Image HQ URL <br> vertical (portrait) 9:16'"></label>
                            <div class="col-sm-8">
                              <input type="text" name="image_portrait_url" placeholder="Enter a url" class="form-control" v-model="video.imagePortraitUrl" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.videoSet.id" />
                              <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imagePortraitUrl', index, indexVideo)" :disabled="content.videoSet.id">Choose File</button>
                            </div>
                          </div>
                          <div class="form-group row">
                            <label for="video_portrait_url" class="col-sm-4 control-label mt-2" v-html="'Video URL <br> vertical (portrait) 9:16'"></label>
                            <div class="col-sm-8">
                              <input type="text" name="video_portrait_url" placeholder="Enter video URL" class="form-control" v-model="video.videoPortraitUrl" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.videoSet.id" />
                              <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoPortraitUrl', index, indexVideo)" :disabled="content.videoSet.id">Choose File</button>
                            </div>
                          </div>
                        </div>
                        <div v-if="['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(campaignObjective)">
                          <div class="form-group row">
                            <label for="video_primary_url" class="col-sm-4 control-label mt-2">Video URL</label>
                            <div class="col-sm-8">
                              <input type="text" name="video_primary_url" placeholder="Enter video URL" class="form-control" v-model="video.videoPrimaryUrl" v-on:blur="loadPreviewEvent($event, index)" :disabled="content.videoSet.id" />
                              <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoPrimaryUrl', index, indexVideo)" :disabled="content.videoSet.id">Choose File</button>
                            </div>
                          </div>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeVideo(index, indexVideo); loadPreviewEvent($event, index)" v-if="indexVideo > 0">Remove Video</button>
                      </fieldset>
                      <div class="row mt-2 mb-2">
                        <div class="col">
                          <span v-if="content.videoSet.id" class="selected-set">{{ content.videoSet.name }}<span class="close" @click="removeVideoSet(index)"><i class="fas fa-times"></i></span></span>
                        </div>
                      </div>
                      <button class="btn btn-primary btn-sm" @click.prevent="addVideo(index)" :disabled="content.id || content.videoSet.id">Add Video</button>
                      <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('video', index)">Load from Sets</button>
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
              <button class="btn btn-primary btn-sm d-none" @click.prevent="addContent()">Add New</button>
            </form>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-primary" @click.prevent="submit" :disabled="!submitState">Save</button>
          </div>
        </div>
      </div>
    </div>

    <div class="modal fade creative-set-modal" id="creative-set-modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
          <div class="col mt-3">
            <h1>Select Creative Set</h1>
          </div>
          <creative-set-sets :type="setType" @selectCreativeSet="selectCreativeSet"></creative-set-sets>
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
    ad: {
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
      for (let i = 0; i < this.contents.length; i++) {
        if (!this.contents[i].brandname || (!this.contents[i].description && !this.contents[i].descriptionSet.id) || !this.contents[i].displayUrl || !this.validURL(this.contents[i].displayUrl) || !this.contents[i].targetUrl || !this.validURL(this.contents[i].targetUrl)) {
          return false
        }

        if (!this.contents[i].titleSet.id) {
          for (let j = 0; j < this.contents[i].titles.length; j++) {
            if (!this.contents[i].titles[j].title) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'IMAGE' && !this.contents[i].imageSet.id) {
          for (let j = 0; j < this.contents[i].images.length; j++) {
            if (!this.contents[i].images[j].imageUrlHQ || !this.validURL(this.contents[i].images[j].imageUrlHQ) || !this.contents[i].images[j].imageUrl || !this.validURL(this.contents[i].images[j].imageUrl) || !this.contents[i].images[j].imageUrlHQState || !this.contents[i].images[j].imageUrlState) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'VIDEO' && !this.contents[i].videoSet.id) {
          for (let j = 0; j < this.contents[i].videos.length; j++) {
            if (['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(this.campaignObjective) && !this.contents[i].videos[j].videoPrimaryUrl) {
              return false
            } else if (!['INSTALL_APP', 'REENGAGE_APP', 'PROMOTE_BRAND'].includes(this.campaignObjective) && (!this.contents[i].videos[j].imagePortraitUrl || !this.contents[i].videos[j].videoPortraitUrl)) {
              return false
            }
          }
        }
      }

      return true
    }
  },
  mounted() {
    console.log('Component mounted.')
    if (this.ad) {
      this.loadPreview(0)
    }
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

      if (this.openingFileSelector === 'videoPrimaryUrl') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorIndexImage].videoPrimaryUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath

        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'imagePortraitUrl') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorIndexImage].imagePortraitUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath

        this.loadPreview(this.fileSelectorIndex)
      }
      if (this.openingFileSelector === 'videoPortraitUrl') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorIndexImage].videoPortraitUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath

        this.loadPreview(this.fileSelectorIndex)
      }

      vm.$modal.hide('imageModal')
    });
  },
  watch: {
    //
  },
  data() {
    let contents = [{
      id: '',
      adType: 'IMAGE',
      titleSet: '',
      titles: [{
        title: this.ad ? this.ad.title : '',
        existing: false
      }],
      displayUrl: this.ad ? this.ad.displayUrl : '',
      targetUrl: this.ad ? this.ad.landingUrl : '',
      descriptionSet: '',
      description: this.ad ? this.ad.description : '',
      brandname: this.ad ? this.ad.sponsoredBy : '',
      imageSet: '',
      images: [{
        imageUrlHQ: this.ad ? this.ad.imageUrlHQ : '',
        imageUrlHQState: true,
        imageUrl: this.ad ? this.ad.imageUrl : '',
        imageUrlState: true,
        existing: false
      }],
      videoSet: '',
      videos: [{
        videoPrimaryUrl: this.ad ? this.ad.videoPrimaryUrl : '',
        videoPortraitUrl: this.ad ? this.ad.videoPortraitUrl : '',
        imagePortraitUrl: this.ad ? this.ad.imagePortraitUrl : '',
        existing: false
      }],
      adPreviews: []
    }];

    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'yahoo',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      campaignObjective: this.campaign && this.campaign.objective ? this.campaign.objective : 'VISIT_WEB',
      postData: {},
      contents: contents,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      fileSelectorIndexImage: 0,
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      },
      adSelectorIndex: 0,
      setType: 'image'
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
    loadCreativeSet(type, index) {
      this.setType = type
      this.adSelectorIndex = index
      $('#creative-set-modal').modal('show')
    },
    selectCreativeSet(set) {
      if (this.setType == 'title') {
        this.contents[this.adSelectorIndex].titleSet = set
        this.loadTitleSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }
      if (this.setType == 'image') {
        this.contents[this.adSelectorIndex].imageSet = set
        this.loadImageSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }
      if (this.setType == 'video') {
        this.contents[this.adSelectorIndex].videoSet = set
        this.loadVideoSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }
      if (this.setType == 'description') {
        this.contents[this.adSelectorIndex].descriptionSet = set
        this.loadDescriptionSets(this.adSelectorIndex).then(() => {
          this.loadPreview(this.adSelectorIndex)
        })
      }

      $('#creative-set-modal').modal('hide')
    },
    addContent() {
      this.contents.push({
        id: '',
        adType: 'IMAGE',
        titleSet: '',
        titles: [{
          title: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        description: '',
        brandname: '',
        imageSet: '',
        images: [{
          imageUrlHQ: '',
          imageUrlHQState: true,
          imageUrl: '',
          imageUrlState: true,
          existing: false
        }],
        videoSet: '',
        videos: [{
          videoPrimaryUrl: '',
          videoPortraitUrl: '',
          imagePortraitUrl: '',
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
    addVideo(index) {
      this.contents[index].videos.push({
        videoPrimaryUrl: '',
        videoPortraitUrl: '',
        imagePortraitUrl: '',
        existing: false
      })
    },
    removeVideo(index, indexVideo) {
      this.contents[index].videos.splice(indexVideo, 1)
    },
    removeImageSet(index) {
      this.contents[index].imageSet = ''
      this.contents[index].images = [{
        imageUrlHQ: '',
        imageUrlHQState: true,
        imageUrl: '',
        imageUrlState: true,
        existing: false
      }]
    },
    removeVideoSet(index) {
      this.contents[index].videoSet = ''
      this.contents[index].videos = [{
        videoPrimaryUrl: '',
        videoPortraitUrl: '',
        imagePortraitUrl: '',
        existing: false
      }]
    },
    removeTitleSet(index) {
      this.contents[index].titleSet = ''
      this.contents[index].titles = [{
        title: '',
        existing: false
      }]
    },
    removeDescriptionSet(index) {
      this.contents[index].descriptionSet = ''
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
    loadTitleSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/title-sets/${this.contents[index].titleSet.id}`).then(response => {
        this.contents[index].titleSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadImageSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/image-sets/${this.contents[index].imageSet.id}`).then(response => {
        this.contents[index].imageSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadVideoSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/video-sets/${this.contents[index].videoSet.id}`).then(response => {
        this.contents[index].videoSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
      });
    },
    loadDescriptionSets(index) {
      this.isLoading = true
      return axios.get(`/creatives/description-sets/${this.contents[index].descriptionSet.id}`).then(response => {
        this.contents[index].descriptionSet.sets = response.data.sets
      }).finally(() => {
        this.isLoading = false
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

      let titles = [], description = ''

      if (this.contents[index].titleSet.id) {
        titles = this.contents[index].titleSet.sets
      } else {
        titles = this.contents[index].titles
      }

      if (this.contents[index].descriptionSet.id) {
        description = this.contents[index].descriptionSet.sets[0].description
      } else {
        description = this.contents[index].description
      }

      if (this.contents[index].adType == 'IMAGE') {
        let images = []

        if (this.contents[index].imageSet.id) {
          images = this.contents[index].imageSet.sets
        } else {
          images = this.contents[index].images
        }

        for (let i = 0; i < titles.length; i++) {
          for (let y = 0; y < images.length; y++) {
            axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
              title: titles[i].title,
              adType: this.contents[index].adType,
              displayUrl: this.contents[index].displayUrl,
              landingUrl: this.contents[index].targetUrl,
              description: description,
              sponsoredBy: this.contents[index].brandname,
              imageUrlHQ: this.contents[index].imageSet.id ? process.env.MIX_APP_URL + (images[y].optimiser == 0 ? '/storage/images/' + images[y].hq_1200x627_image : '/storage/images/creatives/1200x627/' + images[y].hq_image) : images[y].imageUrlHQ,
              imageUrl: this.contents[index].imageSet.id ? process.env.MIX_APP_URL + '/storage/images/' + images[y].image : images[y].imageUrl,
              campaignObjective: this.campaignObjective,
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
      } else {
        let videos = []

        if (this.contents[index].videoSet.id) {
          videos = this.contents[index].videoSet.sets
        } else {
          videos = this.contents[index].videos
        }

        for (let i = 0; i < titles.length; i++) {
          for (let y = 0; y < videos.length; y++) {
            axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
              title: titles[i].title,
              adType: this.contents[index].adType,
              displayUrl: this.contents[index].displayUrl,
              landingUrl: this.contents[index].targetUrl,
              description: description,
              sponsoredBy: this.contents[index].brandname,
              videoPrimaryUrl: videos[y].videoPrimaryUrl,
              videoPortraitUrl: videos[y].videoPortraitUrl,
              imagePortraitUrl: this.contents[index].videoSet.id ? process.env.MIX_APP_URL + '/storage/images/' + videos[y].portrait_image : videos[y].imagePortraitUrl,
              campaignObjective: this.campaignObjective,
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
      }
    },
    submit() {
      this.isLoading = true

      for (let i = 0; i < this.contents.length; i++) {
        if (this.contents[i].titleSet.id) {
          delete this.contents[i].titleSet.sets
        }
        if (this.contents[i].imageSet.id) {
          delete this.contents[i].imageSet.sets
        }
        if (this.contents[i].videoSet.id) {
          delete this.contents[i].videoSet.sets
        }
        if (this.contents[i].descriptionSet.id) {
          delete this.contents[i].descriptionSet.sets
        }

        delete this.contents[i].adPreviews
      }

      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.campaign.advertiser_id,
        campaignObjective: this.campaignObjective,
        contents: this.contents
      }
      let vm = this
      axios.post(`/campaigns/${this.campaign.id}/ad-groups/${this.adGroupId}/ads/store-ad`, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = `/campaigns/${vm.campaign.id}`;
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
