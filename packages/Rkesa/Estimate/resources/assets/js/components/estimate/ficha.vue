<style>
    .block-subheader {
        background-color: #BBB;
        text-align: center;
        font-size: 14px;
        height: 20px;
    }
    .block-inmodal {
        margin-top: 15px;
        margin-bottom: 15px;
    }
    .block-header {
        color: #FFF;
        background-color: #000;
        text-align: center;
        font-size: 15px;
        height: 22px;
    }
    .no-hresize {
        resize: vertical;
    }
    #estimate-ficha .modal-dialog {
        max-width: 980px;
    }
</style>

<template lang="pug">
    div
        #estimate-ficha.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-dialog-centered(role="document")
                .modal-content(v-if="ficha != null")
                    .modal-header
                        h4.modal-title#myModalLabel {{ $t('estimate.Ficha_de_Rendimento') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_ficha()')
                            span(aria-hidden="true") &times;
                    .modal-body
                        .row
                            .col-4
                                | {{ $t('estimate.Find_from_template') }}:
                                v-select(:debounce='250', :on-change='ficha_pattern_select', :on-search='get_ficha_pattern_options', :options='patterns', v-bind:placeholder="$t('estimate.Encontrar_a_ficha')")
                                    template(slot="no-options") {{ $t('template.No_matching_options') }}
                            .col-8
                                button.btn.green(v-on:click='save_ficha()', style='float:right; margin-right: 10px', type='button') {{ $t('estimate.Guardar') }}
                            .col-12
                                .block-inmodal
                                    table#table-ficha-general.table.table-striped.table-hover.table-bordered
                                        thead
                                            tr
                                                th {{ $t('estimate.Description') }}
                                                th {{ $t('estimate.Unidades') }}
                                                th {{ $t('estimate.Quantidade') }}
                                                th {{ $t('estimate.Preco_Custo') }}
                                        tbody
                                            tr
                                                td.align-mid(style='width:60%')
                                                    textarea.form-control.no-hresize(v-model='ficha.ficha_description', rows='5')
                                                td.align-mid(style='width:10%')
                                                    select.form-control(v-model='ficha.ficha_measure')
                                                        template(v-for='unit in units')
                                                            option(:value='unit.id') {{ unit.measure }}
                                                td.align-mid(style='width:15%')
                                                    input.form-control(v-model='ficha.ficha_quantity' type="number")
                                                td.align-mid(style='width:15%')
                                                    | {{ round10(ficha.maodeobra.total_price + ficha.materials.total_price + ficha.equipment.total_price + ficha.subs.total_price) }} {{ $root.current_currency.symbol }}
                            .col-12
                                .block-header.block-inmodal {{ $t('estimate.Rendimentos') }}
                                ficha-resource(:expence_unit_id='ficha.ficha_measure', :fichaid='ficha.id', :mydata='ficha.maodeobra', :headers='{module_name: $t("estimate.Mao_de_obra"), col_name_1: $t("estimate.Performance"), col_name_2: $t("estimate.Correccao")}', :can_correction='true')
                                ficha-resource(:expence_unit_id='ficha.ficha_measure', :fichaid='ficha.id', :mydata='ficha.materials', :headers='{module_name: $t("estimate.Materiais"), col_name_1: $t("estimate.Rendimento"), col_name_2: $t("estimate.Desperdicio")}', :can_correction='true')
                                ficha-resource(:expence_unit_id='ficha.ficha_measure', :fichaid='ficha.id', :mydata='ficha.equipment', :headers='{module_name: $t("estimate.Equipamentos"), col_name_1: $t("estimate.Efficiency"), col_name_2:  $t("estimate.Desperdicio")}', :can_correction='true')
                                ficha-resource(:expence_unit_id='ficha.ficha_measure', :fichaid='ficha.id', :mydata='ficha.subs', :headers='{module_name: $t("estimate.Subempreitadas"), col_name_1: $t("estimate.Efficiency"), col_name_2:  $t("estimate.Correccao")}', :can_correction='true')
                            .col-12
                                .block-header.block-inmodal {{ $t('estimate.Decomp') }}
                                .block-inmodal
                                    .block-subheader {{ $t('estimate.Distribuicao_dos_custos') }}
                                    div
                                        table#table-ficha-distrib.table.table-striped.table-hover.table-bordered
                                            thead
                                                tr
                                                    th
                                                    th {{ $t('estimate.Mao_de_obra') }}
                                                    th {{ $t('estimate.Materiais') }}
                                                    th {{ $t('estimate.Equipamentos') }}
                                                    th {{ $t('estimate.Subemp') }}
                                                    th {{ $t('estimate.TOTAL') }}
                                            tbody
                                                tr
                                                    td.align-mid(style='width:40%')
                                                        | {{ $t('estimate.Valor') }}:
                                                    td.align-mid(style='width:12%')
                                                        label {{ ficha.maodeobra.total_price }} {{ $root.current_currency.symbol }}
                                                    td.align-mid(style='width:12%')
                                                        label {{ ficha.materials.total_price }} {{ $root.current_currency.symbol }}
                                                    td.align-mid(style='width:12%')
                                                        label {{ ficha.equipment.total_price }} {{ $root.current_currency.symbol }}
                                                    td.align-mid(style='width:12%')
                                                        label {{ ficha.subs.total_price }} {{ $root.current_currency.symbol }}
                                                    td.align-mid(style='width:12%')
                                                        label
                                                            | {{ round10(ficha.maodeobra.total_price + ficha.materials.total_price + ficha.equipment.total_price + ficha.subs.total_price) }} {{ $root.current_currency.symbol }}
                                                tr
                                                    td.align-mid(style='width:40%')
                                                        | {{ $t('estimate.Percentagem') }}:
                                                    td.align-mid(style='width:12%')
                                                        label {{ percentage(ficha.maodeobra.total_price) }} %
                                                    td.align-mid(style='width:12%')
                                                        label {{ percentage(ficha.materials.total_price) }} %
                                                    td.align-mid(style='width:12%')
                                                        label {{ percentage(ficha.equipment.total_price) }} %
                                                    td.align-mid(style='width:12%')
                                                        label {{ percentage(ficha.subs.total_price) }} %
                                                    td.align-mid(style='width:12%')
                                                        label 100 %
                            .col-12
                                button.btn.btn-diga(v-on:click='save_ficha()', style='float:right; margin-right: 10px')
                                    | {{ $t('estimate.Guardar') }}
</template>

<script>
import resource from "./ficha_resource.vue"
import {mapGetters} from "vuex";

export default {
    data: function () {
        return {
            ficha: this.mydata,
            patterns: [],
        }
    },
    components: {
        'ficha-resource': resource,
    },
    props: ['mydata'],
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    methods: {
        // select2
        get_ficha_pattern_options(search, loading) {
            loading(true);
            this.$http.get('/api/fichas/search?query=' + search).then(res => {
                var processedData = [];
                res.data.forEach(function(i){
                    processedData.push({'label': i.name, 'value': i.id, 'found_ficha': i});
                });
                this.patterns = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            });
        },
        ficha_pattern_select(res){
            if (res != null) {
                this.ficha.ficha_description = res.found_ficha.description;
                this.ficha.ficha_note = res.found_ficha.note;
                this.ficha.ficha_ppu = res.found_ficha.ppu;
                this.ficha.ficha_quantity = res.found_ficha.quantity;
                this.ficha.ficha_price = res.found_ficha.price;
                this.ficha.ficha_measure = res.found_ficha.estimate_unit_id;
                this.ficha.maodeobra = res.found_ficha.maodeobra;
                this.ficha.materials = res.found_ficha.materials;
                this.ficha.equipment = res.found_ficha.equipment;
                this.ficha.subs = res.found_ficha.subs;
            }
        },
        //
        close_ficha: function(){
            this.$emit('close');
        },
        save_ficha: function(){
            let err = false;
            this.ficha.maodeobra.list.forEach(function(e){ if (!e.selected){ err = true; } });
            this.ficha.materials.list.forEach(function(e){ if (!e.selected){ err = true; } });
            this.ficha.equipment.list.forEach(function(e){ if (!e.selected){ err = true; } });
            this.ficha.subs.list.forEach(function(e){ if (!e.selected){ err = true; } });
            if (err) {
                this.$toastr.w(this.$root.$t("estimate.Need_to_select_yield"), this.$root.$t("template.Warning"));
            } else {
                this.ficha.ficha_ppu = this.round10(this.ficha.maodeobra.total_price + this.ficha.materials.total_price + this.ficha.equipment.total_price + this.ficha.subs.total_price);
                this.$emit('save');
            }
        },
        percentage: function(part){
            let tot = this.ficha.maodeobra.total_price + this.ficha.materials.total_price + this.ficha.equipment.total_price + this.ficha.subs.total_price;
            return tot === 0 ? 0 : this.round10(part * 100 / tot);
        },
        round10: function (num){
            return Math.round(num * 100) / 100;
        },
    },
    watch: {
        mydata: function (data){
            this.ficha = data;
        },
    },
}
</script>