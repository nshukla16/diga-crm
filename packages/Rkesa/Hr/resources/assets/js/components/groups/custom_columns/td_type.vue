<template lang="pug">
div
  span(v-if="row.type === 2") {{ $t('template.Company') }}
  span(v-else) {{ $t('template.Department') }}
</template>

<script>
export default {
  props: ["row"],
  computed: {},
};
</script>