<template lang="pug">
span 
  template(v-if="row.resource") 
    router-link(
      v-if="$root.can_do('resources', 'update') != 0",
      :to="{ name: 'resource_edit', params: { id: row.resource.id } }"
    ) {{ row.resource.name }}
    span(v-else) {{ row.resource.name }}
</template>

<script>
export default {
  props: ["row"],
};
</script>