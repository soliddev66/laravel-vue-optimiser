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
                      <label for="image" class="col-sm-2 control-label mt-2">Image</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Image" class="form-control" v-model="item.image" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageImage', index)">Choose File</button>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="hq_image" class="col-sm-2 control-label mt-2">HQ Image</label>
                      <div class="col-sm-8">
                        <input type="text" name="hq_image" placeholder="HQ Image" class="form-control" v-model="item.hqImage" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageHQImage', index)">Choose File</button>
                      </div>
                    </div>
                  </div>
                  <div v-if="type == 'video'">
                    <div class="form-group row">
                      <label for="image" class="col-sm-2 control-label mt-2">Image</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Image" class="form-control" v-model="item.image" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('videoImage', index)">Choose File</button>
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
        if (this.type == 'image' && (!this.creativeSets[i].image || !this.creativeSets[i].imageHQ)) {
          return false
        }
        if (this.type == 'video' && (!this.creativeSets[i].image || !this.creativeSets[i].video)) {
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
      }
      if (this.openingFileSelector === 'imageHQImage') {
        this.creativeSets[this.fileSelectorIndex].hqImage = selectedFilePath
      }
      if (this.openingFileSelector === 'videoImage') {
        this.creativeSets[this.fileSelectorIndex].image = selectedFilePath
      }
      if (this.openingFileSelector === 'videoVideo') {
        this.creativeSets[this.fileSelectorIndex].vide = selectedFilePath
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
        hqImage: ''
      }]
    } else if (this.type == 'video') {
      creativeSets = [{
        image: '',
        video: ''
      }]
    } else {
      creativeSets = [{
        title: ''
      }]
    }

    if (this.creativeSet) {
      creativeSets = [];
      console.log(this.type)
      for (let i = 0; i < this.creativeSet.sets.length; i++) {
        if (this.type == 'image') {
          creativeSets.push({
            id: this.creativeSet.sets[i].id,
            image: this.creativeSet.sets[i].image,
            hqImage: this.creativeSet.sets[i].hq_image
          })
        }else if (this.type == 'video') {
          creativeSets.push({
            id: this.creativeSet.sets[i].id,
            image: this.creativeSet.sets[i].image,
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
    addSet() {
      if (this.type == 'image') {
        this.creativeSets.push({
          image: '',
          hqImage: ''
        })
      } else if (this.type == 'video') {
        this.creativeSets.push({
          image: '',
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
