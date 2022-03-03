<template lang="pug">
div
  router-link(
    v-if="$root.can_do('products', 'update') != 0",
    :to="{ name: 'products_edit', params: { id: row.id } }"
  ) {{ row.name }}
  span(v-else) {{ row.name }}
</template>

<script>
export default {
  props: ["row"],
};
</script>