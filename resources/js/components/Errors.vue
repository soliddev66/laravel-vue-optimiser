<template>
  <div v-if="errors">
      <ul class="alert alert-danger pl-4">
          <li v-for="error in Object.values(errors.errors || {}).flat()" :key="error">{{ error }}</li>
      </ul>
  </div>
</template>

<script>
export default {
  props: {
    errors: {
      type: Object,
      default: {}
    }
  },
  components: {

  },
  computed: {

  },
  mounted() {
    console.log('Component mounted.')
  },
  data() {
    return {
    }
  },
  methods: {
  }
}
</script>

<style>
</style>
