<template>
  <div class="container-fluid">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Creative Set</h2>
          </div>
          <div class="card-body">
            <errors :errors="errors" v-if="errors.errors"></errors>
            <form class="form-horizontal">
              <fieldset class="mb-4 p-3 rounded border">
                <div class="form-group row">
                  <label for="name" class="col-sm-2 control-label mt-2">Creative Set Name</label>
                  <div class="col-sm-10">
                    <input type="text" name="name" placeholder="Enter a name" class="form-control" v-model="creativeSetName" />
                  </div>
                </div>

                <fieldset class="mb-3 p-3 rounded border" v-for="(item, index) in creativeSets" :key="index">
                  <div v-if="type == 'image'">
                    <div class="form-group row">
                      <label for="image" class="col-sm-2 control-label mt-2">Image (627 x 627)</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Image" class="form-control" v-model="item.image" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageImage', index)">Choose File</button>
                        <small class="text-danger" v-if="!item.imageState">Image is invalid. You might need an 627 x 627 image.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <div class="col-sm-8 offset-sm-2 mt-2">
                        <div class="row">
                          <div class="col-12">
                            <input type="checkbox" id="checkbox" v-model="item.isTiniPNGUsed">
                            <label class="mb-0">The image will be generated, optimized by TinyPNG</label>
                          </div>
                          <div class="col-12" v-if="compressed >= 500">
                            <small class="text-danger">Tinypng reached the limitation and we will be charged.</small>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div v-if="!item.isTiniPNGUsed">
                      <div class="form-group row">
                        <label for="hq_800x800_image" class="col-sm-2 control-label mt-2">HQ Image (800 x 800)</label>
                        <div class="col-sm-8">
                          <input type="text" name="hq_800x800_image" placeholder="Image" class="form-control" v-model="item.hq800x800Image" disabled />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQ800x800Image', index)">Choose File</button>
                          <small class="text-danger" v-if="!item.hq800x800ImageState">Image is invalid. You might need an 800 x 800 image.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="hq_1200x627_image" class="col-sm-2 control-label mt-2">HQ Image (1200 x 627)</label>
                        <div class="col-sm-8">
                          <input type="text" name="hq_1200x627_image" placeholder="Image" class="form-control" v-model="item.hq1200x627Image" disabled />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQ1200x627Image', index)">Choose File</button>
                          <small class="text-danger" v-if="!item.hq1200x627ImageState">Image is invalid. You might need an 1200 x 627 image.</small>
                        </div>
                      </div>
                      <div class="form-group row">
                        <label for="hq_1200x628_image" class="col-sm-2 control-label mt-2">HQ Image (1200 x 628)</label>
                        <div class="col-sm-8">
                          <input type="text" name="hq_1200x628_image" placeholder="Image" class="form-control" v-model="item.hq1200x628Image" disabled />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQ1200x628Image', index)">Choose File</button>
                          <small class="text-danger" v-if="!item.hq1200x628ImageState">Image is invalid. You might need an 1200 x 628 image.</small>
                        </div>
                      </div>
                    </div>
                    <div v-if="item.isTiniPNGUsed">
                      <div class="form-group row">
                        <label for="hq_image" class="col-sm-2 control-label mt-2">HQ Image (1200 x 800)</label>
                        <div class="col-sm-8">
                          <input type="text" name="hq_image" placeholder="HQ Image" class="form-control" v-model="item.hqImage" disabled />
                          <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQImage', index)">Choose File</button>
                          <small class="text-danger" v-if="!item.hqImageState">Image is invalid. You might need an 1200 x 800 image.</small>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="type == 'video'">
                    <div class="form-group row">
                      <label for="image" class="col-sm-2 control-label mt-2">Portrait Image (1080 x 1920)</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Image" class="form-control" v-model="item.portraitImage" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoPortraitImage', index)">Choose File</button>
                        <small class="text-danger" v-if="!item.portraitImageState">Image is invalid. You might need an 1080 x 1920 image.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="image" class="col-sm-2 control-label mt-2">Landscape Image (640 x 360)</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Image" class="form-control" v-model="item.landscapeImage" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoLandscapeImage', index)">Choose File</button>
                        <small class="text-danger" v-if="!item.landscapeImageState">Image is invalid. You might need an 640 x 360 image.</small>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="video" class="col-sm-2 control-label mt-2">Video</label>
                      <div class="col-sm-8">
                        <input type="text" name="video" placeholder="Video" class="form-control" v-model="item.video" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoVideo', index)">Choose File</button>
                      </div>
                    </div>
                  </div>
                  <div v-if="type == 'title'">
                    <div class="form-group row">
                      <label for="title" class="col-sm-2 control-label mt-2">Title</label>
                      <div class="col-sm-8">
                        <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="item.title" />
                      </div>
                    </div>
                  </div>
                  <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeSet(index)" v-if="index > 0">Remove Set</button>
                </fieldset>
                <button class="btn btn-primary btn-sm d-none" @click.prevent="addSet()">Add Set</button>
              </fieldset>
            </form>
          </div>

          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-primary" :disabled="!creativeSetNameState || !creativeSetState" @click.prevent="saveCreativeSet">Save</button>
          </div>
        </div>
      </div>
    </div>

    <modal width="60%" height="80%" name="mediaModal">
      <file-manager v-bind:settings="settings" :props="{
          upload: true,
          viewType: 'grid',
          selectionType: 'single'
      }"></file-manager>
    </modal>
  </div>
</template>

<script>
import _ from 'lodash'
import Select2 from 'v-select2-component'
import Loading from 'vue-loading-overlay'
import axios from 'axios'

import 'vue-loading-overlay/dist/vue-loading.css'

export default {
  props: {
    type: {
      type: String,
      default: 'image'
    },
    compressed: {
      type: Number,
      default: 0
    },
    action: {
      type: String,
      default: 'create'
    },
    creativeSet: {
      type: Object,
      default: null
    }
  },
  components: {
    Loading,
  },
  computed: {
    creativeSetNameState() {
      return this.creativeSetName !== ''
    },

    creativeSetState() {
      for (let i = 0; i < this.creativeSets.length; i++) {
        if (this.type == 'image' && (!this.creativeSets[i].image || !this.creativeSets[i].imageState
          || (this.creativeSets[i].isTiniPNGUsed && (!this.creativeSets[i].hqImage || !this.creativeSets[i].hqImageState))
          || (!this.creativeSets[i].isTiniPNGUsed && (!this.creativeSets[i].hq800x800Image || !this.creativeSets[i].hq800x800ImageState || !this.creativeSets[i].hq1200x627Image || !this.creativeSets[i].hq1200x627ImageState || !this.creativeSets[i].hq1200x628Image || !this.creativeSets[i].hq1200x628ImageState)))) {
          return false
        }
        if (this.type == 'video' && (!this.creativeSets[i].portraitImage || !this.creativeSets[i].portraitImageState || !this.creativeSets[i].landscapeImage || !this.creativeSets[i].landscapeImageState || !this.creativeSets[i].video)) {
          return false
        }
        if (this.type == 'title' && !this.creativeSets[i].title) {
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
      const selectedFilePath = value[0].path

      if (this.openingFileSelector === 'imageImage') {
        this.creativeSets[this.fileSelectorIndex].image = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 627, 627).then(result => {
          this.creativeSets[this.fileSelectorIndex].imageState = result
        });
      }
      if (this.openingFileSelector === 'imageHQImage') {
        this.creativeSets[this.fileSelectorIndex].hqImage = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 1200, 800).then(result => {
          this.creativeSets[this.fileSelectorIndex].hqImageState = result
        });
      }
      if (this.openingFileSelector === 'imageHQ800x800Image') {
        this.creativeSets[this.fileSelectorIndex].hq800x800Image = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 800, 800).then(result => {
          this.creativeSets[this.fileSelectorIndex].hq800x800ImageState = result
        });
      }
      if (this.openingFileSelector === 'imageHQ1200x627Image') {
        this.creativeSets[this.fileSelectorIndex].hq1200x627Image = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 1200, 627).then(result => {
          this.creativeSets[this.fileSelectorIndex].hq1200x627ImageState = result
        });
      }
      if (this.openingFileSelector === 'imageHQ1200x628Image') {
        this.creativeSets[this.fileSelectorIndex].hq1200x628Image = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 1200, 628).then(result => {
          this.creativeSets[this.fileSelectorIndex].hq1200x628ImageState = result
        });
      }
      if (this.openingFileSelector === 'videoPortraitImage') {
        this.creativeSets[this.fileSelectorIndex].portraitImage = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 1080, 1920).then(result => {
          this.creativeSets[this.fileSelectorIndex].portraitImageState = result
        });
      }
      if (this.openingFileSelector === 'videoLandscapeImage') {
        this.creativeSets[this.fileSelectorIndex].landscapeImage = selectedFilePath
        this.validImageSize(process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath, 640, 360).then(result => {
          this.creativeSets[this.fileSelectorIndex].landscapeImageState = result
        });
      }
      if (this.openingFileSelector === 'videoVideo') {
        this.creativeSets[this.fileSelectorIndex].video = selectedFilePath
      }
      vm.$modal.hide('mediaModal')
    });
  },
  watch: {

  },
  data() {
    let creativeSets = null

    if (this.type == 'image') {
      creativeSets = [{
        image: '',
        imageState: true,
        isTiniPNGUsed: false,
        hqImage: '',
        hqImageState: true,
        hq800x800Image: '',
        hq800x800ImageState: true,
        hq1200x627Image: '',
        hq1200x627ImageState: true,
        hq1200x628Image: '',
        hq1200x628ImageState: true
      }]
    } else if (this.type == 'video') {
      creativeSets = [{
        portraitImage: '',
        portraitImageState: true,
        landscapeImage: '',
        landscapeImageState: true,
        video: ''
      }]
    } else {
      creativeSets = [{
        title: ''
      }]
    }

    if (this.creativeSet) {
      creativeSets = [];

      for (let i = 0; i < this.creativeSet.sets.length; i++) {
        if (this.type == 'image') {
          creativeSets.push({
            id: this.creativeSet.sets[i].id,
            image: this.creativeSet.sets[i].image,
            imageState: true,
            isTiniPNGUsed: this.creativeSet.sets[i].hq_image != null && this.creativeSet.sets[i].hq_image != '',
            hqImage: this.creativeSet.sets[i].hq_image,
            hqImageState: true,
            hq800x800Image: this.creativeSet.sets[i].hq_800x800_image,
            hq800x800ImageState: true,
            hq1200x627Image: this.creativeSet.sets[i].hq_1200x627_image,
            hq1200x627ImageState: true,
            hq1200x628Image: this.creativeSet.sets[i].hq_1200x628_image,
            hq1200x628ImageState: true
          })
        }else if (this.type == 'video') {
          creativeSets.push({
            id: this.creativeSet.sets[i].id,
            portraitImage: this.creativeSet.sets[i].portrait_image,
            portraitImageState: true,
            landscapeImage: this.creativeSet.sets[i].landscape_image,
            landscapeImageState: true,
            video: this.creativeSet.sets[i].video
          })
        } else {
          creativeSets.push({
            id: this.creativeSet.sets[i].id,
            title: this.creativeSet.sets[i].title
          });
        }
      }
    }

    return {
      errors: {},
      isLoading: false,
      fullPage: true,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      creativeSetName: this.creativeSet ? this.creativeSet.name : '',
      creativeSetType: this.type,
      creativeSets: creativeSets,

      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'en'
      }
    }
  },
  methods: {
    openChooseFile(name, index) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show('mediaModal')
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
    addSet() {
      if (this.type == 'image') {
        this.creativeSets.push({
          image: '',
          imageState: true,
          isTiniPNGUsed: false,
          hqImage: '',
          hqImageState: true,
          hq800x800Image: '',
          hq800x800ImageState: true,
          hq1200x627Image: '',
          hq1200x627ImageState: true,
          hq1200x628Image: '',
          hq1200x628ImageState: true
        })
      } else if (this.type == 'video') {
        this.creativeSets.push({
          portraitImage: '',
          portraitImageState: true,
          landscapeImage: '',
          landscapeImageState: true,
          video: ''
        })
      } else {
        this.creativeSets.push({
          title: ''
        })
      }
    },
    removeSet(index) {
      this.creativeSets.splice(index, 1);
    },
    saveCreativeSet() {
      this.isLoading = true

      this.postData = {
        creativeSetName: this.creativeSetName,
        creativeSetType: this.creativeSetType,
        creativeSets: this.creativeSets
      }

      let url = '/creatives';

      if (this.action == 'edit') {
        url += '/update/' + this.creativeSet.id;
      }

      axios.post(url, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          this.$dialog.alert('Save successfully!').then(function(dialog) {
            window.location = '/creatives';
          });
          this.errors = {}
        }
      }).catch(error => {
        this.errors = error.response.data
      }).finally(() => {
        this.isLoading = false
      })
    }
  }
}
</script>
