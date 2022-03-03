<template lang="pug">
    div
        div(v-for="r in row.manufacturers")
            div(v-for="o in r.orders")
                router-link(:to="{name: 'project_show', params: {id: r.project_id, orderId: o.id, ManufacId: r.manufacturer_id }}") {{ o.name }}
</template>

<script>
export default {
    props: ['row'],
    created() {
        //
    },
}
</script>