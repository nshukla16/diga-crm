<template lang="pug">
    div {{ $t('template.History_' + row.event) }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    methods: {
    },
}
</script>