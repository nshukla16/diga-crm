<style>
</style>

<template lang="pug">
div
  div(v-for="vs in values")
    input.mr-2(
      type="radio",
      v-bind:value="vs.value",
      v-model="v",
      v-on:change="$emit('change', { emit_action: emit_action, action: action, value: vs.value })",
      v-bind:disabled="!vs.enabled"
    )
    | {{ vs.title }}
</template>

<script>
export default {
  data() {
    return {};
  },
  props: ["action", "value", "values", "emit_action"],
  computed: {
    v: {
      get: function () {
        return this.value;
      },
      set: function (newValue) {
        //
      },
    },
  },
  methods: {},
};
</script>