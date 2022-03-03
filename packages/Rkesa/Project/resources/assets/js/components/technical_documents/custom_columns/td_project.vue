<template lang="pug">
    router-link(:to="{name: 'project_show', params: {id: row.project_id}}") {{ row.project.name }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: ['row'],
    computed: {

    },
}
</script>