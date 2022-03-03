<template lang="pug">
    router-link(:to="{name: 'contact_show', query: {service_id: row.id}, params: {id: row.client_contact_id}}") {{ $root.service_number(row) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>