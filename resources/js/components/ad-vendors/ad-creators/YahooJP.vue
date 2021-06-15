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
                        <div class="row mb-2" v-for="(headline, indexTitle) in content.headlines" :key="indexTitle">
                          <div class="col-sm-8">
                            <input type="text" name="headline" placeholder="Enter a headline" class="form-control" v-model="headline.headline" :disabled="content.titleSet.id" />
                          </div>
                          <div class="col-sm-4">
                            <button type="button" class="btn btn-light" @click.prevent="removeTitle(index, indexTitle)" v-if="indexTitle > 0" :disabled="content.titleSet.id"><i class="fa fa-minus"></i></button>
                            <button type="button" class="btn btn-primary" @click.prevent="addTitle(index)" v-if="indexTitle + 1 == content.headlines.length" :disabled="content.id || content.titleSet.id"><i class="fa fa-plus"></i></button>
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
                      <label for="ad_type" class="col-sm-4 control-label mt-2">Ad Type</label>
                      <div class="col-sm-8">
                        <div class="btn-group btn-group-toggle">
                          <label class="btn bg-olive" :class="{ active: content.adType === 'RESPONSIVE_IMAGE_AD' }">
                            <input type="radio" name="ad_type" autocomplete="off" value="RESPONSIVE_IMAGE_AD" v-model="content.adType"> IMAGE
                          </label>
                          <label class="btn bg-olive" :class="{ active: content.adType === 'RESPONSIVE_VIDEO_AD' }">
                            <input type="radio" name="ad_type" autocomplete="off" value="RESPONSIVE_VIDEO_AD" v-model="content.adType"> VIDEO
                          </label>
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
                        <textarea class="form-control" rows="3" placeholder="Enter description" v-model="content.description" :disabled="content.descriptionSet.id"></textarea>
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
                    <div class="form-group row" v-if="content.adType === 'RESPONSIVE_IMAGE_AD'">
                      <label for="image" class="col-sm-4 control-label mt-2">Images</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Images" class="form-control mb-2" disabled="disabled" v-model="content.imagePath" />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imagePath', index)" :disabled="content.imageSet.id">Choose Files</button>
                        <button class="btn btn-primary btn-sm" data-toggle="modal" data-target=".creative-set-modal" @click.prevent="loadCreativeSet('image', index)">Load from Sets</button>
                      </div>
                      <div class="col-sm-8 offset-sm-4" v-if="!content.imageSet.id">
                        <small class="text-danger" v-for="(image, indexImage) in content.images" :key="indexImage">
                          <span class="d-inline-block" v-if="image.image && !image.state">Image {{ image.image }} is invalid. You might need an 1200 x 628 image.</span>
                        </small>
                      </div>
                      <div class="col-sm-8 offset-sm-4 mt-2">
                        <span v-if="content.imageSet.id" class="selected-set">{{ content.imageSet.name }}<span class="close" @click="removeImageSet(index)"><i class="fas fa-times"></i></span></span>
                      </div>
                    </div>
                    <div v-if="content.adType === 'RESPONSIVE_VIDEO_AD'">
                      <fieldset class="mb-3 p-3 rounded border" v-for="(video, videoIndex) in content.videos" :key="videoIndex">
                        <div class="form-group row">
                          <label for="video" class="col-sm-4 control-label mt-2">Video</label>
                          <div class="col-sm-8">
                            <input type="text" name="video" placeholder="Video" class="form-control" disabled="disabled" v-model="video.videoPath" />
                            <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoPath', index, videoIndex)">Choose File</button>
                          </div>
                        </div>

                        <div class="form-group row">
                          <label for="video_thumbnail_url" class="col-sm-4 control-label mt-2">Thumbnail</label>
                          <div class="col-sm-8">
                            <input type="text" name="video_thumbnail_url" placeholder="Enter thumbnail URL" class="form-control" disabled="disabled" v-model="video.videoThumbnailPath" />
                            <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoThumbnailPath', index, videoIndex)">Choose File</button>
                          </div>
                          <div class="col-sm-8 offset-sm-4">
                            <small class="text-danger" v-if="video.videoThumbnailPath && !video.videoThumbnailState">
                              <span class="d-inline-block">Image {{ video.videoThumbnailPath }} is invalid. You might need an 640 x 360 image.</span>
                            </small>
                          </div>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeVideo(index, videoIndex)" v-if="videoIndex > 0">Remove Video</button>
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
                    <div v-if="content.adType == 'RESPONSIVE_IMAGE_AD'">
                        <section v-for="(title, indexTitle) in content.headlines" :key="indexTitle">
                          <section v-for="(image, indexImage) in content.images" :key="indexImage">
                            <div class="row no-gutters mb-2" v-if="image.url">
                              <div class="col-sm-5" >
                                <img :src="image.url" class="card-img-top h-100">
                              </div>
                              <div class="col-sm-7">
                                <div class="card-body">
                                  <h3 class="card-title">{{ title.headline }}</h3>
                                  <h6 class="card-text mt-5"><i>{{ content.principal }}</i></h6>
                                </div>
                              </div>
                            </div>
                          </section>
                        </section>
                      </div>
                      <div v-if="content.adType == 'RESPONSIVE_VIDEO_AD'">
                        <section v-for="(title, indexTitle) in content.headlines" :key="indexTitle">
                          <section v-for="(video, indexVideo) in content.videos" :key="indexVideo">
                            <div class="row no-gutters mb-2" v-if="video.videoThumbnailUrl">
                              <div class="col-sm-5" >
                                <img :src="video.videoThumbnailUrl" class="card-img-top h-100">
                              </div>
                              <div class="col-sm-7">
                                <div class="card-body">
                                  <h3 class="card-title">{{ title.headline }}</h3>
                                  <h6 class="card-text mt-5"><i>{{ content.principal }}</i></h6>
                                </div>
                              </div>
                            </div>
                          </section>
                        </section>
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

        if (!this.contents[i].titleSet.id) {
          for (let j = 0; j < this.contents[i].headlines.length; j++) {
            if (!this.contents[i].headlines[j].headline) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'RESPONSIVE_VIDEO_AD' && !this.contents[i].videoSet.id) {
          if (this.contents[i].videos.length == 0) {
            return false
          }

          for (let j = 0; j < this.contents[i].videos.length; j++) {
            if (this.contents[i].videos[j].mediaId) {
              continue
            }
            if (!this.contents[i].videos[j].videoPath || !this.contents[i].videos[j].videoThumbnailPath|| !this.contents[i].videos[j].videoThumbnailState) {
              return false
            }
          }
        }

        if (this.contents[i].adType == 'RESPONSIVE_IMAGE_AD' && !this.contents[i].imageSet.id) {
          if (this.contents[i].images.length == 0) {
            return false
          }

          for (let j = 0; j < this.contents[i].images.length; j++) {
            if (this.contents[i].images[j].mediaId) {
              continue
            }
            if (!this.contents[i].images[j].image || !this.contents[i].images[j].state) {
              return false
            }
          }
        }
      }

      return true
    }
  },
  mounted() {
    let vm = this
    this.$root.$on('fm-selected-items', (values) => {
      if (this.openingFileSelector === 'imagePath') {
        this.contents[this.fileSelectorIndex].images = [];
        let paths = []
        for (let i = 0; i < values.length; i++) {
          this.contents[this.fileSelectorIndex].images.push({
            image: values[i].path,
            url: process.env.MIX_APP_URL + '/storage/images/' + values[i].path,
            state: this.validDimensions(values[i].width, values[i].height, 1200, 628),
            existing: false
          })
          paths.push(values[i].path)
        }
        this.contents[this.fileSelectorIndex].imagePath = paths.join(';')
      } else if (this.openingFileSelector === 'videoPath') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoPath = values[0].path
      } else if (this.openingFileSelector === 'videoThumbnailPath') {
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoThumbnailPath = values[0].path
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoThumbnailUrl = process.env.MIX_APP_URL + '/storage/images/' + values[0].path
        this.contents[this.fileSelectorIndex].videos[this.fileSelectorVideoIndex].videoThumbnailState = this.validDimensions(values[0].width, values[0].height, 640, 360)
      }
      vm.$modal.hide('imageModal')
    });
  },
  watch: {

  },
  data() {
    let contents = [{
      id: '',
      adType: 'RESPONSIVE_IMAGE_AD',
      titleSet: '',
      headlines: [{
        headline: '',
        existing: false
      }],
      displayUrl: '',
      targetUrl: '',
      descriptionSet: '',
      description: '',
      principal: '',
      imageSet: '',
      images: [],
      videoSet: '',
      videos: [{
        videoPath: '',
        videoThumbnailPath: '',
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
      },
      adSelectorIndex: 0,
      setType: 'image'
    }
  },
  methods: {
    openChooseFile(name, index, videoIndex) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index

      this.fileSelectorVideoIndex = videoIndex

      if (name == 'imagePath') {
        this.$root.$store.commit('fm/setSelectionType', 'multiple')
      } else {
        this.$root.$store.commit('fm/setSelectionType', 'single')
      }

      this.$modal.show('imageModal')
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
          this.contents[this.adSelectorIndex].headlines = this.contents[this.adSelectorIndex].titleSet.sets.map(item => {
            return {
              headline: item.title
            }
          })
        })
      }
      if (this.setType == 'image') {
        this.contents[this.adSelectorIndex].imageSet = set
        this.loadImageSets(this.adSelectorIndex).then(() => {
          this.contents[this.adSelectorIndex].images = this.contents[this.adSelectorIndex].imageSet.sets.map(item => {
            return {
              url: process.env.MIX_APP_URL + (item.optimiser == 0 ? '/storage/images/' + item.hq_1200x628_image : '/storage/images/creatives/1200x628/' + item.hq_image)
            }
          })
        })
      }
      if (this.setType == 'video') {
        this.contents[this.adSelectorIndex].videoSet = set
        this.loadVideoSets(this.adSelectorIndex).then(() => {
          this.contents[this.adSelectorIndex].videos = this.contents[this.adSelectorIndex].videoSet.sets.map(item => {
            return {
              videoThumbnailUrl: process.env.MIX_APP_URL + '/storage/images/' + item.portrait_image
            }
          })
        })
      }
      if (this.setType == 'description') {
        this.contents[this.adSelectorIndex].descriptionSet = set
      }

      $('#creative-set-modal').modal('hide')
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
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validDimensions(fileWidth, fileHeight, width, height) {
      return fileWidth == width && fileHeight == height
    },
    addContent() {
      this.contents.push({
        id: '',
        adType: 'RESPONSIVE_IMAGE_AD',
        titleSet: '',
        headlines: [{
          headline: '',
          existing: false
        }],
        displayUrl: '',
        targetUrl: '',
        descriptionSet: '',
        description: '',
        principal: '',
        imageSet: '',
        images: [{
          image: '',
          existing: false
        }],
        videoSet: '',
        videos: [{
          videoPath: '',
          videoThumbnailPath: '',
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

    addVideo(index) {
      this.contents[index].videos.push({
        videoPath: '',
        videoThumbnailPath: '',
        existing: false
      })
    },
    removeVideo(index, videoIndex) {
      this.contents[index].videos.splice(videoIndex, 1)
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
