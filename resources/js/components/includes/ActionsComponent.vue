<template>
  <div v-if="!classes['is-rule']">
    <button class="border" :class="classes" @click="click(data)" title="Update">
      <span>
        <i aria-hidden="true" class="fas fa-play" :class="{ 'fa-stop': ['ACTIVE', 'RUNNING'].includes(data.status) }"></i>
    </span>
    </button>
    <a v-if="classes['btn-add-new-ad']" class="btn btn-sm btn-default border" :data-status="data.status" :href="`/campaigns/${data.campaign_id}/ad-groups/${data.ad_group_id}/ads/create`">
      <i aria-hidden="true" title="Create new ad" class="fas fa-plus"></i>
    </a>
    <a v-if="classes['btn-clone-ad']" class="btn btn-sm btn-default border" :href="`/campaigns/${data.campaign_id}/ad-groups/${data.ad_group_id}/ads/${data.ad_id}/clone`">
      <i aria-hidden="true" title="Clone ad" class="fas fa-clone"></i>
    </a>
  </div>
  <div v-else>
    <a class="btn btn-sm btn-default border" :href="'/rules/edit/' + data.id"><i class="fas fa-edit"></i> Edit</a>
    <a class="btn btn-sm btn-default border" :href="`/rules/${data.id}/logs`"><i class="fas fa-history"></i> Log</a>
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
