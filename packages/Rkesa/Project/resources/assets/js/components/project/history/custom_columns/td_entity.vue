<template lang="pug">
    div {{ this.cleanTheName(row.auditable_type) }}
</template>

<script>
export default {
    props: ['row'],
    computed: {
    },
    methods: {
        cleanTheName(oldName){
            let splitted = oldName.split("\\");
            return this.$root.$t('project.' + splitted[splitted.length - 1]) || splitted[splitted.length - 1];
        },
    },
}
</script>