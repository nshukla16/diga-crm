<template lang="pug">
span
  a(v-if="row.client", :href="'tel:' + row.client.phone") {{ row.client.phone }}
</template>

<script>
export default {
  props: ["row"],
  computed: {},
};
</script>