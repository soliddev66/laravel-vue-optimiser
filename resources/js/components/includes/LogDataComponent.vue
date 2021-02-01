<template>
  <div v-if="data">
    <div v-for="(value, name) in data">
      <span>{{ name }}:</span>
      <ul v-if="typeof value === 'string' && value.startsWith('[')">
        <li v-for="(info, key) in JSON.parse(value)[0]">
          <span>{{ key }}:</span>
          <ul v-if="typeof info === 'object'">
            <li v-for="i in info">
              <span>{{ i.ruleConditionType }}: {{ i.passed ? 'Passed' : 'Not Passed' }}</span>
            </li>
          </ul>
          <span v-else>
            {{ info }}
          </span>
        </li>
      </ul>
      <span v-else>
        {{ value }}
      </span>
    </div>
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
