<template lang="pug">
span
  router-link(
    v-if="row.client_id > 0",
    :to="{ name: 'company_show', params: { id: row.client_id } }"
  ) {{ $t('client.Perfil') }}
  router-link(
    v-else,
    :to="{ name: 'company_create_group', params: { group_id: row.id } }"
  ) {{ $t('template.create_profile') }}
</template>

<script>
export default {
  props: ["row"],
  computed: {},
};
</script>