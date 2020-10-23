<template>
  <div class="container">
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Ad</h2>
          </div>
          <div class="card-body">
            <form class="form-horizontal">
              <h2 class="pb-2">General information</h2>
              <div class="row">
                <div class="col-sm-6">
                  <div class="form-group row">
                    <label for="title" class="col-sm-4 control-label mt-2">Title</label>
                    <div class="col-sm-8">
                      <input type="text" name="title" placeholder="Enter a title" class="form-control" v-model="title" />
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="brand_name" class="col-sm-4 control-label mt-2">Company Name</label>
                    <div class="col-sm-8">
                      <input type="text" name="brand_name" placeholder="Enter a brandname" class="form-control" v-model="brandname" />
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="description" class="col-sm-4 control-label mt-2">Description</label>
                    <div class="col-sm-8">
                      <textarea class="form-control" rows="3" placeholder="Enter description" v-model="description"></textarea>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="display_url" class="col-sm-4 control-label mt-2">Display Url</label>
                    <div class="col-sm-8 text-center">
                      <input type="text" name="display_url" placeholder="Enter a url" class="form-control" v-model="displayUrl" />
                      <small class="text-danger" v-if="displayUrl && !displayUrlState">URL is invalid. You might need http/https at the beginning.</small>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="target_url" class="col-sm-4 control-label mt-2">Target Url</label>
                    <div class="col-sm-8 text-center">
                      <input type="text" name="target_url" placeholder="Enter a url" class="form-control" v-model="targetUrl" />
                      <small class="text-danger" v-if="targetUrl && !targetUrlState">URL is invalid. You might need http/https at the beginning.</small>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="image_hq_url" class="col-sm-4 control-label mt-2">Image HQ URL</label>
                    <div class="col-sm-8">
                      <input type="text" name="image_hq_url" placeholder="Enter a url" class="form-control" v-model="imageUrlHQ" />
                    </div>
                    <div class="col-sm-8 offset-sm-4">
                      <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('hqModal')">Choose File</button>
                      <!-- <input type="file" ref="imageHQ" @change="selectedHQFile" accept="image/*"> -->
                    </div>
                    <div class="col-sm-8 offset-sm-4 text-center">
                      <small class="text-danger" v-if="imageUrlHQ && !imageUrlHQState">URL is invalid. You might need http/https at the beginning.</small>
                      <small class="text-danger" v-if="imageHQ.size && !imageHQState">Image is invalid. You might need an 1200x627 image.</small>
                    </div>
                  </div>
                  <div class="form-group row">
                    <label for="image_url" class="col-sm-4 control-label mt-2">Image URL</label>
                    <div class="col-sm-8">
                      <input type="text" name="image_url" placeholder="Enter a url" class="form-control" v-model="imageUrl" />
                    </div>
                    <div class="col-sm-8 offset-sm-4">
                      <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('imageModal')">Choose File</button>
                      <!-- <input type="file" ref="image" @change="selectedFile" accept="image/*"> -->
                    </div>
                    <div class="col-sm-8 offset-sm-4 text-center">
                      <small class="text-danger" v-if="imageUrl && !imageUrlState">URL is invalid. You might need http/https at the beginning.</small>
                      <small class="text-danger" v-if="image.size && !imageState">Image is invalid. You might need an 627x627 image.</small>
                    </div>
                  </div>
                </div>
                <div class="col-sm-6">
                  <h1>Preview</h1>
                  <div v-html="previewData"></div>
                </div>
              </div>
            </form>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-primary" @click.prevent="submit" :disabled="!titleState || !brandnameState || !descriptionState || !displayUrlState || !targetUrlState || !imageUrlHQState || !imageUrlState">Save</button>
          </div>
        </div>
      </div>
    </div>
    <modal width="60%" height="80%" name="hqModal">
      <file-manager v-bind:settings="settings" :props="{
          upload: true,
          viewType: 'grid',
          selectionType: 'single'
      }"></file-manager>
    </modal>
    <modal width="60%" height="80%" name="imageModal">
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
    campaign: {
      type: Object,
      default: null
    },
    adGroupId: {
      type: String,
      default: null
    }
  },
  components: {
    Loading,
    Select2
  },
  computed: {
    titleState() {
      return this.title !== ''
    },
    brandnameState() {
      return this.brandname !== ''
    },
    descriptionState() {
      return this.description !== ''
    },
    displayUrlState() {
      return this.displayUrl !== '' && this.validURL(this.displayUrl)
    },
    targetUrlState() {
      return this.targetUrl !== '' && this.validURL(this.targetUrl)
    },
    imageUrlHQState() {
      return (this.imageUrlHQ !== '' && this.validURL(this.imageUrlHQ)) || this.imageHQState
    },
    imageHQState() {
      return this.imageHQ.size !== '' && this.validSize(this.imageHQ, 'HQ')
    },
    imageUrlState() {
      return (this.imageUrl !== '' && this.validURL(this.imageUrl)) || this.imageState
    },
    imageState() {
      return this.image.size !== '' && this.validSize(this.image, '')
    }
  },
  mounted() {
    console.log('Component mounted.')
    let vm = this
    this.$root.$on('fm-selected-items', (value) => {
      const selectedFilePath = value[0].path
      if (this.openingFileSelector === 'hqModal') {
        this.imageUrlHQ = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
      }
      if (this.openingFileSelector === 'imageModal') {
        this.imageUrl = process.env.MIX_APP_URL + '/storage/images/' + selectedFilePath
      }
      vm.$modal.hide(this.openingFileSelector)
    });
  },
  watch: {
    title: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    displayUrl: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    targetUrl: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    description: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    brandname: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    imageUrlHQ: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000),
    imageUrl: _.debounce(function(newVal) {
      this.loadPreview()
    }, 2000)
  },
  data() {
    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'yahoo',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      postData: {},
      title: '',
      displayUrl: '',
      targetUrl: '',
      description: '',
      brandname: '',
      imageUrlHQ: '',
      imageHQ: {
        size: '',
        height: '',
        width: ''
      },
      imageUrl: '',
      image: {
        size: '',
        height: '',
        width: ''
      },
      previewData: '',
      openingFileSelector: '',
      settings: {
        baseUrl: '/file-manager', // overwrite base url Axios
        windowsConfig: 2, // overwrite config
        lang: 'end'
      }
    }
  },
  methods: {
    openChooseFile(name) {
      this.openingFileSelector = name
      this.$modal.show(name)
    },
    validURL(str) {
      var pattern = /^(http(s)?:\/\/.)?(www\.)?[-a-zA-Z0-9@:%._\+~#=]{2,256}\.[a-z]{2,6}\b([-a-zA-Z0-9@:%_\+.~#?&//=]*)/g;
      return !!pattern.test(str);
    },
    validSize(image, type) {
      if (type === 'HQ') {
        if (image.width === 1200 && image.height === 627) {
          return true;
        }
      } else {
        if (image.width === 627 && image.height === 627) {
          return true
        }
      }
      return false;
    },
    selectedHQFile() {
      let file = this.$refs.imageHQ.files[0];
      if (!file || file.type.indexOf('image/') !== 0) return;
      this.imageHQ.size = file.size;
      let reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = evt => {
        let img = new Image();
        img.onload = () => {
          this.imageHQ.width = img.width;
          this.imageHQ.height = img.height;
          if (this.validSize(this.imageHQ, 'HQ')) {
            let formData = new FormData();
            formData.append('file', this.$refs.imageHQ.files[0]);
            axios.post('/general/upload-files', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }).then((response) => {
                this.imageUrlHQ = response.data.path.replace('public', 'storage')
              })
              .catch((err) => {
                alert(err);
              });
          }
        }
        img.src = evt.target.result;
      }
      reader.onerror = evt => {
        console.error(evt);
      }
    },
    selectedFile() {
      let file = this.$refs.image.files[0];
      if (!file || file.type.indexOf('image/') !== 0) return;
      this.image.size = file.size;
      let reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onload = evt => {
        let img = new Image();
        img.onload = () => {
          this.image.width = img.width;
          this.image.height = img.height;
          if (this.validSize(this.image, '')) {
            let formData = new FormData();
            formData.append('file', this.$refs.image.files[0]);
            axios.post('/general/upload-files', formData, {
                headers: {
                  'Content-Type': 'multipart/form-data'
                }
              }).then((response) => {
                this.imageUrl = response.data.path.replace('public', 'storage')
              })
              .catch((err) => {
                alert(err);
              });
          }
        }
        img.src = evt.target.result;
      }
      reader.onerror = evt => {
        console.error(evt);
      }
    },
    loadPreview() {
      this.isLoading = true
      axios.post(`/general/preview?provider=${this.selectedProvider}&account=${this.selectedAccount}`, {
        title: this.title,
        displayUrl: this.displayUrl,
        landingUrl: this.targetUrl,
        description: this.description,
        sponsoredBy: this.brandname,
        imageUrlHQ: this.imageUrlHQ,
        imageUrl: this.imageUrl,
        campaignLanguage: this.campaignLanguage
      }).then(response => {
        this.previewData = response.data.replace('height="800"', 'height="450"').replace('width="400"', 'width="100%"')
      }).catch(err => {
        console.log(err)
      }).finally(() => {
        this.isLoading = false
      })
    },
    submit() {
      this.isLoading = true
      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.campaign.advertiser_id,
        campaignID: this.campaign.id,
        displayUrl: this.displayUrl,
        targetUrl: this.targetUrl,
        title: this.title,
        brandname: this.brandname,
        description: this.description,
        imageUrlHQ: this.imageUrlHQ,
        imageUrl: this.imageUrl
      }

      axios.post(`/campaigns/${this.campaign.id}/ad-groups/${this.adGroupId}/ads/store-ad`, this.postData).then(response => {
        if (response.data.errors) {
          alert(response.data.errors[0])
        } else {
          alert('Save successfully!');
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

<style>
.select2-container .select2-selection--single {
  min-height: 28px;
  height: auto;
}
</style>
