<template lang="pug">
    div
        router-link(v-if="row.client" :to="{ name: 'company_show', params: {id: row.client_id }}") {{ row.client.name }}
        template(v-else) {{row.name}}
</template>

<script>
export default {
    props: ['row'],
}
</script>