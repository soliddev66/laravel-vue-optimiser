<template>
  <div class="images" v-viewer>
    <img :src="data.image" width="auto" height="100px">
  </div>
</template>

<script>
import 'viewerjs/dist/viewer.css';

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
