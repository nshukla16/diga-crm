<template lang="pug">
    div {{ $root.formatFinanceValue(row.contract_price) +' '+ get_currency_format($root.currencies[row.contract_currency]) }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: ['row'],
    computed: {

    },
    methods:{
        get_currency_format(currency){
            return currency.code;
        },
    }
}
</script>