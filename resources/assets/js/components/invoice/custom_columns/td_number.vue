<template lang="pug">
    div
        router-link.ml-2(v-if="$root.can_do('invoices', 'read') != 0", :style="[row.is_canceled === true ? {'text-decoration': 'line-through'} : {'text-decoration': 'unset'}]", :to="{name: 'invoice_pdf', params: {id: row.id}}" target="_blank") {{ row.invoice_no }}
        template(v-else)
            span(:style="[row.is_canceled === true ? {'text-decoration': 'line-through'} : {'text-decoration': 'unset'}]") {{ row.invoice_no }}
</template>

<script>
export default {
    props: ['row'],
}
</script>