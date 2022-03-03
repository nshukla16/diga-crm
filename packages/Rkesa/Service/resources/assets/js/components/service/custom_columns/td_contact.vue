<template lang="pug">
    router-link(v-if="row.client_contact", :to="{ name: $root.contact_or_client_show_route(), params: {id: row.client_contact_id }}") {{ $root.fullName(row.client_contact) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>