<template lang="pug">
    router-link(:to="{name: 'project_history', params: {id: row.id}}") {{ $t('template.See_all') }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: ['row'],
    computed: {

    },
}
</script>