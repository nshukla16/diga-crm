<template lang="pug">
    span {{ row.client_contact_emails.map(e => e.email).join(', ') }}
</template>

<script>
export default {
    props: ['row'],
}
</script>