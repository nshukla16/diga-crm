<template lang="pug">
    div
        router-link.btn.btn-secondary(:to="{name: 'carrier_edit', params: {id: row.id}}") {{ $t('template.Edit') }}
</template>

<script>
export default {
    props: ['row'],
}
</script>