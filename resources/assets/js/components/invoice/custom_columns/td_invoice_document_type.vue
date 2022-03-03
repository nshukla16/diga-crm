<template lang="pug">
    div(v-if="row.invoice_document_type") {{ row.invoice_document_type.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>