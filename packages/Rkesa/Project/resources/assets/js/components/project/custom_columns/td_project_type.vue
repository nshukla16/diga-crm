<template lang="pug">
    div {{ typesById[row.project_type_id].name }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    computed: {
        ...mapGetters({
            typesById: 'getProjectTypesById',
        }),
    },
}
</script>