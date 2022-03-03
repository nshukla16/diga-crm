<template lang="pug">
    div
        div(v-if="row.operator!=='balance'") {{ row.sum }}{{format_currency(globalSettings.settings.price_currency)}}
        div(v-if="row.operator==='balance' && row.sum > 0") {{$t('template.from_balance')}} {{ row.sum }}{{format_currency(globalSettings.settings.price_currency)}}
        div(v-if="row.operator==='balance' && row.sum < 0") {{$t('template.to_balance')}} {{ row.sum*(-1) }}{{format_currency(globalSettings.settings.price_currency)}}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    methods: {
        format_currency(currency){
            return {
                'eur': '€',
                'rub': '₽',
            }[currency];
        },
    },
    computed:{
        ...mapGetters({
            globalSettings: 'getGlobalSettings',
        }),
    }
}
</script>