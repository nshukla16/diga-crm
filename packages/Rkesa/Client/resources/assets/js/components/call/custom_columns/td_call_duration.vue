<template lang="pug">
    div(v-if="row.duration" class='text-center') {{ row.duration + " " + this.$root.$t('client.Call_duration_seconds') }}
</template>

<script>
export default {
    components: {
    },
    props: ['row'],
}
</script>