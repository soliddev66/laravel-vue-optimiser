<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <label class="p-2" :class="{ 'bg-primary': currentStep === 1 }">Traffic Source Setup</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 2 }">Tracker Setup</label>
            <i class="fas fa-arrow-right"></i>
            <label class="p-2" :class="{ 'bg-primary': currentStep === 3 }">Connect and Finish</label>
          </div>
          <div class="card-body" v-if="currentStep == 1">
            <p v-for="provider in providers">
              <label>
                <input type="radio" v-model="selectedProvider" :value="provider.slug"></input> {{ provider.label }}
              </label>
            </p>
          </div>
          <div class="card-body" v-if="currentStep == 2">
            <label v-for="tracker in trackers">
              <input type="radio" v-model="selectedTracker" :value="tracker.slug"></input> {{ tracker.label }}
            </label>
            <div class="form-group mt-3" v-if="selectedTracker === 'redtrack'">
              <label>API key</label>
              <input type="text" name="api_key" v-model="redtrackKey" class="form-control">
            </div>
          </div>
          <div class="card-body text-center" v-if="currentStep == 3">
            <p>Integration completed successfully</p>
            <p>System is syncing your stats.</p>
          </div>
          <div class="card-footer d-flex justify-content-end">
            <div class="d-flex justify-content-start flex-grow-1" v-if="currentStep < 3 && currentStep > 1">
              <button type="button" class="btn btn-primary" @click.prevent="currentStep = currentStep - 1">Back</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 1">
              <button type="button" class="btn btn-default mr-2" @click.prevent="submitStep1(0)">I don't use a Tracker</button>
              <button type="button" class="btn btn-primary" @click.prevent="submitStep1(1)">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 2">
              <button type="button" class="btn btn-primary" v-if="redtrackKey" @click.prevent="submitStep2(1)">Next</button>
            </div>
            <div class="d-flex justify-content-end" v-if="currentStep === 3">
              <button type="button" class="btn btn-primary mr-2" @click.prevent="currentStep = 1">Add another integration</button>
              <a href="/home" class="btn btn-success">Take me to Dashboard</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    providers: {
      type: Array,
      default: []
    },
    trackers: {
      type: Array,
      default: []
    },
    step: {
      type: Number,
      default: 1
    }
  },
  mounted() {
    console.log('Component mounted.')
    this.currentStep = this.step
    console.log(this.providers)
  },
  data() {
    return {
      currentStep: 1,
      redtrackKey: '',
      selectedProvider: 'yahoo',
      selectedTracker: 'redtrack'
    }
  },
  methods: {
    submitStep1(useTracker) {
      window.location = `/login/${this.selectedProvider}?user_tracker=${useTracker}`
    },
    submitStep2(useTracker) {
      window.location = `/login/${this.selectedTracker}?api_key=${this.redtrackKey}`
    }
  }
}
</script>

<style>
</style>
