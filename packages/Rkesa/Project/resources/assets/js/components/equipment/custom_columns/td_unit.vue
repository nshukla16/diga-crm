<template lang="pug">
    div {{ unitsById[row.estimate_unit_id].measure }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    computed: {
        ...mapGetters({
            unitsById: 'getEstimateUnitsById',
        }),
    },
}
</script>
