<template lang="pug">
    div
        router-link.btn.btn-secondary(style="margin-right: 5px", :to="{name: 'my_works_edit', params: {id: row.id}}") {{ $t('service.Service_edit') }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
    },
}
</script>