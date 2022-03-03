<template lang="pug">
    router-link(v-if="row.client_contact" :to="{ name: 'contact_show', params: {id: row.client_contact_id }}") {{ row.client_contact.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>