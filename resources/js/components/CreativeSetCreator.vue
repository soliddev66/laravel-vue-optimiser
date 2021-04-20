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
                <div v-if="type == 'media'">
                  <fieldset class="mb-3 p-3 rounded border" v-for="(media, index) in creativeSets" :key="index">
                    <div class="form-group row">
                      <label for="image" class="col-sm-2 control-label mt-2">Image</label>
                      <div class="col-sm-8">
                        <input type="text" name="image" placeholder="Image" class="form-control" v-model="media.image" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('mediaImage', index)">Choose File</button>
                      </div>
                    </div>
                    <div class="form-group row">
                      <label for="video" class="col-sm-2 control-label mt-2">Video</label>
                      <div class="col-sm-8">
                        <input type="text" name="video" placeholder="Video" class="form-control" v-model="media.video" disabled />
                        <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('mediaVideo', index)">Choose File</button>
                      </div>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm" @click.prevent="removeSet(index)" v-if="index > 0">Remove Set</button>
                  </fieldset>
                  <button class="btn btn-primary btn-sm" @click.prevent="addSet()">Add Set</button>
                </div>
              </fieldset>
            </form>
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
      default: 'media'
    },
    action: {
      type: String,
      default: 'create'
    }
  },
  components: {
    Loading,
  },
  computed: {
  },
  mounted() {
    console.log('Component mounted.')

    let vm = this
    this.$root.$on('fm-selected-items', (value) => {
      const selectedFilePath = value[0].path

      if (this.openingFileSelector === 'mediaImage') {
        this.creativeSets[this.fileSelectorIndex].image = selectedFilePath
      }
      if (this.openingFileSelector === 'mediaVideo') {
        this.creativeSets[this.fileSelectorIndex].video = selectedFilePath
      }
      console.log(this.creativeSets)
      vm.$modal.hide('mediaModal')
    });
  },
  watch: {

  },
  data() {
    return {
      errors: {},
      isLoading: false,
      fullPage: true,
      openingFileSelector: '',
      fileSelectorIndex: 0,
      creativeSetName: this.creativeSet ? this.creativeSet.name : '',
      creativeSets: [{
        image: '',
        video: ''
      }],
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
      this.creativeSets.push({
        image: '',
        video: ''
      })
    },
    removeSet(index) {
      this.creativeSets.splice(index, 1);
    }
  }
}
</script>
