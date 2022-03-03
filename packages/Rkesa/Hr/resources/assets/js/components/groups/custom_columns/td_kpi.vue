<template lang="pug">
    span 
        router-link(v-if="averageKpi >= 0", :to="{ name: 'users_and_groups_by_user', params: { id: row.id, isGroup: true } }") {{$root.formatFinanceValue(averageKpi)}} %
</template>

<script>
export default {
    props: ['row'],
    computed: {
        averageKpi(){           
            var sum = 0;
            for( var i = 0; i < this.row.kpi.length; i++ ){
                sum += parseInt( this.row.kpi[i].kpi, 0 ); 
            }

            var avg = sum/this.row.kpi.length;

            return avg;
        }
    }
}
</script>