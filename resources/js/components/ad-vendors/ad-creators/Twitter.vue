<template>
  <section>
    <div class="vld-parent">
      <loading :active.sync="isLoading" :can-cancel="true" :is-full-page="fullPage"></loading>
    </div>
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header">
            <h2 class="mb-0">New Twitter Ad</h2>
          </div>
          <div class="card-body">
            <form class="form-horizontal">
              <h2 class="pb-2">General information</h2>
              <fieldset class="mb-3 p-3 rounded border" v-for="(card, index) in cards" :key="index">
                <div class="form-group row">
                  <label for="tweet_text" class="col-sm-2 control-label mt-2">Tweet Text</label>
                  <div class="col-lg-10 col-xl-8">
                    <div class="row mb-2" v-for="(tweetText, indexText) in card.tweetTexts" :key="indexText">
                      <div class="col-sm-8">
                        <input type="text" name="tweet_text" placeholder="Enter texts" class="form-control" v-model="tweetText.text" />
                      </div>
                      <div class="col-sm-4">
                        <button type="button" class="btn btn-light" @click.prevent="removeTweetText(index, indexText)" v-if="indexText > 0"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-primary" @click.prevent="addTweetText(index)" v-if="indexText + 1 == card.tweetTexts.length"><i class="fa fa-plus"></i></button>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="tweet_nullcast" class="col-sm-2 control-label mt-2">Tweet Nullcast</label>
                  <div class="col-lg-4 col-xl-3">
                    <div class="btn-group btn-group-toggle">
                      <label class="btn bg-olive" :class="{ active: card.tweetNullcast }">
                        <input type="radio" name="tweet_nullcast" id="tweetNullcast1" autocomplete="off" :value="true" v-model="card.tweetNullcast">TRUE
                      </label>
                      <label class="btn bg-olive" :class="{ active: !card.tweetNullcast }">
                        <input type="radio" name="tweet_nullcast" id="tweetNullcast2" autocomplete="off" :value="false" v-model="card.tweetNullcast">FALSE
                      </label>
                    </div>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_name" class="col-sm-2 control-label mt-2">Card Name</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_name" placeholder="Enter a name" class="form-control" v-model="card.name" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_media" class="col-sm-2 control-label mt-2">Card Media Image</label>
                  <div class="col-sm-8">
                    <input type="text" name="card_media" placeholder="Media Image" class="form-control" v-model="card.mediaPath" disabled />
                  </div>
                  <div class="col-sm-8 offset-sm-2">
                    <button type="button" class="btn btn-sm btn-default border" @click="openChooseFile('cardMedia', index)">Choose File</button>
                  </div>
                  <div class="col-sm-8 offset-sm-2">
                    <small class="text-danger" v-for="(image, indexImage) in card.media" :key="indexImage">
                      <span class="d-inline-block" v-if="image.image && !image.state">Image {{ image.image }} is invalid. A minimum image width of 800px and a width:height aspect ratio of either 1:1 or 1.91:1 is required.</span>
                    </small>
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_website_title" class="col-sm-2 control-label mt-2">Card Website Title</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_website_title" placeholder="Enter website title" class="form-control" v-model="card.websiteTitle" />
                  </div>
                </div>
                <div class="form-group row">
                  <label for="card_website_url" class="col-sm-2 control-label mt-2">Card Website URL</label>
                  <div class="col-lg-10 col-xl-8">
                    <input type="text" name="card_website_url" placeholder="Enter a website URL" class="form-control" v-model="card.websiteUrl" />
                  </div>
                </div>
                <div class="row" v-if="index > 0">
                  <div class="col text-right">
                    <button class="btn btn-warning btn-sm" @click.prevent="removeCard(index)">Remove</button>
                  </div>
                </div>
              </fieldset>
              <button class="btn btn-primary btn-sm d-none" @click.prevent="addCard()">Add New</button>
            </form>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <button type="button" class="btn btn-primary" @click.prevent="submit" :disabled="!submitState">Save</button>
          </div>
        </div>
      </div>
    </div>
    <modal width="60%" height="80%" name="cardMedia">
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
      for (let i = 0; i < this.cards.length; i++) {
        if (!this.cards[i].name || !this.cards[i].mediaPath || !this.cards[i].websiteTitle || !this.cards[i].websiteUrl) {
          return false
        }
        for (let j = 0; j < this.cards[i].tweetTexts.length; j++) {
          if (!this.cards[i].tweetTexts[j]) {
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
      if (this.openingFileSelector === 'cardMedia') {
        let paths = []
        this.cards[this.fileSelectorIndex].media = []

        for (let i = 0; i < values.length; i++) {
          this.cards[this.fileSelectorIndex].media.push({
            image: values[i].path,
            state: this.validImage(values[i].width, values[i].height)
          })

          paths.push(values[i].path)
        }
        this.cards[this.fileSelectorIndex].mediaPath = paths.join(';')
      }
      vm.$modal.hide(this.openingFileSelector)
    });
  },
  watch: {

  },
  data() {
    console.log(this.ad)
    return {
      isLoading: false,
      fullPage: true,
      selectedProvider: 'twitter',
      selectedAccount: this.campaign ? this.campaign.open_id : '',
      postData: {},
      cards: [{
        name: '',
        media: [],
        mediaPath: '',
        websiteTitle: '',
        websiteUrl: '',
        tweetTexts: [{
          text: this.ad ? this.ad['full_text'] : ''
        }],
        tweetNullcast: this.ad ? this.ad['nullcast'] : true
      }],
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
    validImage(width, height)  {
      return width >= 800 && (width / height == 1 || width / height == 1.91)
    },
    openChooseFile(name, index = 0) {
      this.openingFileSelector = name
      this.fileSelectorIndex = index
      this.$modal.show(name)
    },
    addCard() {
      this.cards.push({
        name: '',
        media: [],
        mediaPath: '',
        websiteTitle: '',
        websiteUrl: '',
        tweetTexts: [{
          text: ''
        }],
        tweetNullcast: ''
      })
    },
    removeCard(index) {
      this.cards.splice(index, 1);
    },
    addTweetText(index) {
      this.cards[index].tweetTexts.push({ text: '' })
    },
    removeTweetText(index, indexText) {
      this.cards[index].tweetTexts.splice(indexText, 1)
    },
    submit() {
      this.isLoading = true
      this.postData = {
        provider: this.selectedProvider,
        account: this.selectedAccount,
        selectedAdvertiser: this.campaign.advertiser_id,
        cards: this.cards
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
