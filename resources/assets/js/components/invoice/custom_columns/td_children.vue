<template lang="pug">
    div(v-if="row.children")
        p(v-for="child in row.children")
            router-link.ml-2(v-if="$root.can_do('invoices', 'read') != 0", :style="[child.is_canceled === true ? {'text-decoration': 'line-through'} : {'text-decoration': 'unset'}]", :to="{name: 'invoice_pdf', params: {id: child.id}}" target="_blank") {{ child.invoice_no }}
            template(v-else)
                span(:style="[child.is_canceled === true ? {'text-decoration': 'line-through'} : {'text-decoration': 'unset'}]") {{ child.invoice_no }}
</template>

<script>
export default {
    props: ['row'],
}
</script>