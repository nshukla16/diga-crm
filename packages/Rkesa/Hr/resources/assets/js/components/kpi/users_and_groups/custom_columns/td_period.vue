<template lang="pug">
    span {{$t('hr.' + row.period.name)}}
</template>

<script>
export default {
    props: ['row'],
    mounted() {
    }
}
</script>