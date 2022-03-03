<template lang="pug">
    a(v-if="row.invoice_file" :href="row.invoice_file" target="_blank") {{row.invoice_file_name}}
</template>

<script>
export default {
    props: ['row'],
}
</script>