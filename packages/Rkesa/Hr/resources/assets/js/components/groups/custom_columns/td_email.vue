<template lang="pug">
span
  a(v-if="row.client", :href="'mailto:' + row.client.email") {{ row.client.email }}
</template>

<script>
export default {
  props: ["row"],
  computed: {},
};
</script>