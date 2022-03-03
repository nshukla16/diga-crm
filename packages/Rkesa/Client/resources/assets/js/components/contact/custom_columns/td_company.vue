<template lang="pug">
    router-link(v-if="row.client", :to="{ name: 'company_show', params: {id: row.client_id }}") {{ row.client.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>