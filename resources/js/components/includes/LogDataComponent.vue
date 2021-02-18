<template>
  <div v-if="data">
    <div v-for="(value, name) in data">
      <span>{{ name }}:</span>
      <ul v-if="(typeof value == 'string' && (value.startsWith('[') || value.startsWith('{'))) || typeof value === 'object'">
        <li v-for="(info, key) in JSON.parse(value)">
          <span>{{ key == 0 ? 'info' : key }}: </span>
          <ul v-if="typeof info == 'object'">
            <li v-for="(i, t) in info">
              <span>{{ t == 0 ? 'info' : t }}: </span>
              <ul v-if="typeof i == 'object'">
                <li v-for="(ii, tt) in i">
                  <span>{{ tt == 0 ? 'info' : tt }}: </span>
                  <ul v-if="typeof ii == 'object'">
                    <li v-for="(iii, ttt) in ii">
                      <span>{{ ttt == 0 ? 'info' : ttt }}: </span>
                      <ul v-if="typeof iii == 'object'">
                        <li v-for="(iiii, tttt) in iii">
                          <span>{{ tttt }}: {{ iiii }}</span>
                        </li>
                      </ul>
                      <span v-else>{{ ttt }}: {{ iii }}</span>
                    </li>
                  </ul>
                  <span v-else>{{ ii }}</span>
                </li>
              </ul>
              <span v-else>{{ i }}</span>
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
