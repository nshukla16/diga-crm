<template lang="pug">
    div(v-if="row.client_contact && row.client_contact.client")
        router-link(:to="{name: 'company_show', params: {id: row.client_contact.client_id}}") {{ row.client_contact.client.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>
