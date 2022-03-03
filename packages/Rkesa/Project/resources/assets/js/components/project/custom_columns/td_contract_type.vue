<template lang="pug">
    div {{ get_contract_type(row.contract_type) }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    methods: {
        get_contract_type(val){
            if (val == 0) return this.$root.$t('project.With_transition');
            if (val == 1) return this.$root.$t('project.Direct');
        },
    },
}
</script>