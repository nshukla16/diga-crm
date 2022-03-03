<template lang="pug">
    div
        ul
            li(v-for="mod in json.modules") {{$t('template.module-'+mod.name)}}: {{$t('template.from')}} 
                span {{dateFormat(mod.current_subscription_date_start)}} {{$t('template.to')}} {{dateFormat(mod.current_subscription_date_end)}} 

</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';

export default {
    props: ['row'],
    methods: {
        dateFormat(datetime) {
            return moment(datetime).format('MMM Do YYYY');
        },
        format_currency(currency){
            return {
                'eur': '€',
                'rub': '₽',
            }[currency];
        },
    },
    computed:{
        json(){
            if (this.row.data){
                return JSON.parse(this.row.data);
            }
            else 
            {
                return null;
            }
        },
        ...mapGetters({
            globalSettings: 'getGlobalSettings',
        }),
    }
}
</script>