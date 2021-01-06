<template>
  <div>
    <button class="border" :class="classes" @click="click(data)" title="Update">
      <span>
        <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': ['ACTIVE', 'RUNNING'].includes(data.status) }"></i>
    </span>
    </button>
    <a v-if="classes['btn-add-new-ad']" class="btn btn-sm btn-default border" :data-status="data.status" :href="`/campaigns/${data.campaign_id}/ad-groups/${data.ad_group_id}/ads/create`">
      <i aria-hidden="true" title="Create new ad" class="fas fa-plus"></i>
    </a>
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
      }),
    }
  }
}
</script>
