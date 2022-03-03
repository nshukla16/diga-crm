<template lang="pug">
    div
        ul
            li(v-for="(type, index) in row.types") {{ $t('hr.' + type.type.name) }}
</template>

<script>
export default {
    props: ['row'],
    mounted() {
    }
}
</script>