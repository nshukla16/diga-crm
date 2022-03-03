<template lang="pug">
    div(v-if='row.service && row.service.client_contact')
        router-link(v-if="row.service.client_contact.can_be_readed", :to="{name: $root.contact_or_client_show_route(), params: {id: row.service.client_contact.id }}") {{ $root.fullName(row.service.client_contact) }}
        span(v-else) {{ $root.fullName(row.service.client_contact) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>