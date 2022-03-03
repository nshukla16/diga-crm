<template lang="pug">
    router-link(:to="{name: 'expences_show', params: {id: row.id}}") {{ row.invoice_number }}
</template>

<script>
export default {
    props: ['row'],
}
</script>