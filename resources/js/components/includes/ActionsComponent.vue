<template>
  <button class="border" :class="classes" @click="click(data)" title="Update">
    <span>
        <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': data.status == 'ACTIVE' }"></i>
    </span>
  </button>
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
      }),
    },
  }
}
</script>
