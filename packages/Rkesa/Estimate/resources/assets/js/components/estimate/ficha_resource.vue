<style>
    .estimate-ficha-resource input{
        width: 100%;
    }
    .estimate-ficha-resource .actions{
        text-align: center;
        vertical-align: middle;
    }
    .estimate-ficha-resource .actions i{
        cursor: pointer;
    }
    .align-mid{
        text-align: center;
    }
    /*.v-select input[type=search], .v-select input[type=search]:focus{
        width: 1px !important;
    }*/
</style>

<template lang="pug">
    .estimate-ficha-resource
        .block-inmodal
            .block-subheader {{ headers.module_name }}
            div
                table.table.table-striped.table-hover.table-bordered
                    thead
                        tr
                            th(style='width: 300px;') {{ headers.module_name }}
                            th {{ $t('estimate.Unidades') }}
                            th {{ $t('estimate.Preco') }}
                            th {{ headers.col_name_1 }}
                            th(v-if='can_correction') {{ headers.col_name_2 }}
                            //+'/'+units_by_id[expence_unit_id].measure
                            th {{ $t('estimate.Valor') }}
                            th {{ $t('estimate.Actions') }}
                    tbody
                        template(v-if='resources.list.length != 0')
                            tr(v-for='resource in resources.list')
                                td
                                    v-select(v-model='resource.name', :debounce='250', :on-change='ficha_resource_select(resource)', :on-search='get_ficha_resource_options', :options='bases', v-bind:placeholder="$t('estimate.Escolha_a_opcao')")
                                        template(slot="no-options") {{ $t('template.No_matching_options') }}
                                td
                                    select.form-control(v-model='resource.estimate_unit_id')
                                        template(v-for='unit in units')
                                            option(:value='unit.id') {{ unit.measure }}
                                td
                                    input.form-control(v-model='resource.price', v-on:change='calculate_price()', type='number')
                                td
                                    input.form-control(v-model='resource.quantity', v-on:change='calculate_price()', type='number')
                                td(v-if='can_correction')
                                    input.form-control(v-model='resource.correction', v-on:change='calculate_price()', type='number')
                                td
                                    | {{ resource.total_price }}
                                td.actions
                                    i.fa.fa-times(v-on:click='remove_row(resource)')
                        tr(v-else='')
                        tr
                            td(v-bind:colspan='can_correction ? 7 : 6')
                                button.btn.green(v-on:click='add_row()', type='button')
                                    i.fa.fa-plus
                        tr
                            td.align-mid(v-bind:colspan='can_correction ? 5 : 4')
                                | {{ $t('estimate.TOTAL') }}
                            td.align-mid
                                label {{ resources.total_price }} {{ $root.current_currency.symbol }}
                            td
</template>

<script>
import {mapGetters} from "vuex";

export default {
    inherit: true,
    data: function () {
        return {
            resources: this.mydata,
            bases: [],
            selected: false,
        }
    },
    props: ['mydata', 'headers', 'fichaid', 'can_correction', 'expence_unit_id'],
    mounted(){
        this.calculate_price();
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
            units_by_id: 'getEstimateUnitsById',
        }),
    },
    methods: {
        // select2
        get_ficha_resource_options(search, loading) {
            loading(true);
            this.$http.get('/api/ficha_resources?search=' + search + '&type=' + this.resources.list[0].resource_type).then(res => {
                var processedData = [];
                res.data.forEach(function(i){
                    i.label = i.name;
                    i.value = i.id;
                    processedData.push(i);
                });
                this.bases = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        ficha_resource_select(res){
            if (typeof res.name === 'object') {
                if (res.name !== null) { // selected
                    if (res.name.efficiency_estimate_unit_id !== this.expence_unit_id) {
                        this.$toastr.w(this.$root.$t("estimate.Expence_units_are_different"), this.$root.$t("template.Warning"));
                    }
                    res.estimate_unit_id = res.name.estimate_unit_id;
                    res.price = res.name.price;
                    res.quantity = res.name.quantity;
                    res.name = res.name.name;
                    res.selected = true;
                    this.calculate_price();
                } else { // cleared
                    res.selected = false;
                }
            }
        },
        // ficha resource
        add_row: function(){
            let $line = {
                estimate_unit_id: null,
                estimate_line_ficha_id: this.fichaid,
                name: null,
                price: null,
                quantity: null,
                total_price: null,
                is_pattern: false,
                selected: false,
                // Random id
                id: (new Date()).getTime(),
            };
            $line.correction = this.headers.col_name_2 === this.$root.$t("estimate.Desperdicio") ? 0 : 1;
            $line.resource_type = [this.$root.$t("estimate.Mao_de_obra"), this.$root.$t("estimate.Materiais"), this.$root.$t("estimate.Equipamentos"), this.$root.$t("estimate.Subempreitadas")].indexOf(this.headers.module_name);
            this.resources.list.push($line);
        },
        remove_row: function(element){
            let $del_index = this.resources.list.indexOf(element);
            this.resources.list.splice($del_index, 1);
            this.calculate_price();
        },
        calculate_price: function(){
            let resources_total_price = 0;
            let $this = this;
            this.resources.list.forEach(function(resource){
                resource.total_price = resource.price * resource.quantity * resource.correction;
                if ($this.headers.col_name_2 === $this.$root.$t("estimate.Desperdicio")){
                    resource.total_price /= 100;
                    resource.total_price += resource.price * resource.quantity;
                }
                resource.total_price = $this.round10(resource.total_price);
                resources_total_price += resource.total_price;
            });
            this.resources.total_price = $this.round10(resources_total_price);
        },
        round10: function (num){
            return Math.round(num * 100) / 100;
        },
    },
    watch: {
        mydata: function () {
            this.resources = this.mydata;
            this.calculate_price();
        },
    },
}
</script>