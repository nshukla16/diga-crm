<template lang="pug">
    div {{ statusesById[row.project_status_id].name }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    computed: {
        ...mapGetters({
            statusesById: 'getProjectStatusesById',
        }),
    },
}
</script>