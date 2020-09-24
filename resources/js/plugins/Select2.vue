<template>
  <select>
    <slot></slot>
  </select>
</template>

<script>
export default {
  props: ['options', 'value'],
  template: '#select2-template',
  mounted: function() {
    var vm = this;
    $(this.$el)
      // init select2
      .select2({ data: this.options })
      .val(this.value)
      .trigger('change')
      // emit event on change.
      .on('change', function() {
        vm.$emit('input', this.value);
      });
  },
  watch: {
    value: function(value) {
      // update value
      if (typeof value === 'object') {
        // multiselect, need a deep compare
        if (JSON.stringify(value) !== JSON.stringify(value)) {
          $(this.$el)
            .val(value)
            .trigger('change')
        }
      } else {
        $(this.$el)
          .val(value)
          .trigger('change')
      }
    },
    options: function(options) {
      // update options
      $(this.$el)
        .empty()
        .select2({ data: options });
    }
  },
  destroyed: function() {
    $(this.$el)
      .off()
      .select2('destroy');
  }
}
</script>

<style>
</style>
