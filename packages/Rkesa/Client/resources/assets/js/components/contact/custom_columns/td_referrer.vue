<template lang="pug">
    div
        template(v-if="row.client_referrer") {{ row.client_referrer.title}}
        template(v-else) {{ $t('client.Unknown') }}
</template>

<script>
export default {
    props: ['row'],
}
</script>