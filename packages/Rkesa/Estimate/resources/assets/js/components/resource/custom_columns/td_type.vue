<template lang="pug">
    span {{ get_type_from_id(row.resource_type) }}
</template>

<script>
export default {
    props: ['row'],
    methods: {
        get_type_from_id(id){
            switch (id){
            case 0:
                return this.$root.$t('estimate.Mao_de_obra');
            case 1:
                return this.$root.$t('estimate.Materiais');
            case 2:
                return this.$root.$t('estimate.Equipamentos');
            case 3:
                return this.$root.$t('estimate.Subempreitadas');
            }
        },
    },
}
</script>