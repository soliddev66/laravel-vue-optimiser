<template>
  <div>
    <span>{{ data.name }}</span>
    <br>
    <small>ID: {{ data.ad_id }}</small>
  </div>
</template>

<script>
export default {
  props: {
    data: {},
    name: {},
    click: {
      type: Function,
      default: () => {}
    },
    classes: {
      type: Object,
      default: () => ({
        'btn': true,
        'btn-primary': true,
        'btn-sm': true,
        'btn-add-new-ad': false,
        'btn-clone-ad': false
      }),
    }
  },
  mounted() {
    console.log(this.classes);
  }
}
</script>
