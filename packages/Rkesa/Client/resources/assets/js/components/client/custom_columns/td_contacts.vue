<template lang="pug">
    div
        span(v-for="(contact, i) in row.client_contacts")
            router-link(:to="{ name: $root.contact_or_client_show_route(), params: {id: contact.id }}") {{ $root.fullName(contact) }}
            span.mr-1(v-if="row.client_contacts.length - 1 != i") ,
</template>

<script>
export default {
    props: ['row'],
}
</script>