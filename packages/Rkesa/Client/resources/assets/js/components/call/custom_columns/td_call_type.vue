<template lang="pug">
    div {{ row.event==='NOTIFY_OUT_END' ? $t('client.Call_outgoing') : $t('client.Call_incoming') }}
</template>

<script>
export default {
    components: {
    },
    props: ['row'],
}
</script>