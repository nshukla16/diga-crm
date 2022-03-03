<template lang="pug">
    div
        span(v-if="row.status=='submitted_for_settlement' || row.status=='approved' || row.status=='balance'") {{ $t('template.success') }}
        span(v-if="row.status=='invoice'") {{ $t('template.waiting_for_payment') }}
</template>

<script>

export default {
    props: ['row'],
    methods: {
    },
}
</script>