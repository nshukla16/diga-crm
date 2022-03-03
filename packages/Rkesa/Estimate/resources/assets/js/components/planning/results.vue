<template lang="pug">
div.diga-container.p-4
    h2 {{ $t('estimate.Result_information') }}
    div.table-responsive
        table.table.table-striped(style='width: 100%;')
            thead
                tr
                    td(colspan='3') {{ $t('estimate.Cost_price') }}
                    td(rowspan='2' style="border-left: 1px solid #eee;border-right: 1px solid #eee;") {{ $t('estimate.Estimate_price') }}
                    td(colspan='2') {{ $t('estimate.Gross_profit') }}
                tr
                    td {{ $t('estimate.Mao_de_obra') }}
                    td {{ $t('estimate.Materiais') }}
                    td {{ $t('estimate.Movement') }}
                    td {{ $root.current_currency.symbol }}
                    td %
            tbody
                tr
                    td {{ total_maodeobra }}
                    td {{ total_material }}
                    td {{ total_dislocation }}
                    td {{ price }}
                    td {{ round10(price - total_maodeobra - total_material - total_dislocation) }}
                    td
                        | {{ price==0 ? 0 : round10(100 - (total_maodeobra + total_material + total_dislocation)/price*100) }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['currentEstimate', 'total_maodeobra', 'total_material', 'total_dislocation', 'price'],
    data(){
        return {
        }
    },
    created(){

    },    
    methods:{
        round10(num){
            return Math.round(num * 100) / 100;
        },
    },
    computed:{

    }
}
</script>