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
</style>

<template lang="pug">
    .portlet.light
        .portlet-body
            .row(v-if="currentFicha")
                section.col-12.mb-3
                    div.diga-container.p-4
                        h2 {{ $t('estimate.Main_information') }}
                        .row
                            .col-6
                                fieldset.form-group(:class="{ 'has-error': errors.has('name') }")
                                    label.control-label {{ $t('estimate.Name') }}
                                    input.form-control(name="name", v-validate="'required'", type="text", v-model="currentFicha.name" v-bind:data-vv-as="$t('estimate.Name').toLowerCase()")
                                    span.help-block(v-show="errors.has('name')") {{ errors.first('name') }}
                            .col-6
                                fieldset.form-group
                                    label.control-label {{ $t('estimate.Unidades') }}
                                    select(class="form-control", v-model="currentFicha.estimate_unit_id")
                                        option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                            .col-12
                                fieldset.form-group
                                    label.control-label {{ $t('estimate.Description') }}
                                    textarea.form-control(v-model="currentFicha.description")
                section.col-12.mb-3
                    div.diga-container.p-4
                        h2 {{ $t('estimate.Rendimentos') }}
                        ficha-resource(:expence_unit_id='currentFicha.estimate_unit_id', :fichaid='currentFicha.id', :mydata='currentFicha.maodeobra', :headers='{module_name: $t("estimate.Mao_de_obra"), col_name_1: $t("estimate.Performance"), col_name_2: $t("estimate.Correccao")}', :can_correction='false')
                        ficha-resource(:expence_unit_id='currentFicha.estimate_unit_id', :fichaid='currentFicha.id', :mydata='currentFicha.materials', :headers='{module_name: $t("estimate.Materiais"), col_name_1: $t("estimate.Rendimento"), col_name_2: $t("estimate.Desperdicio")}', :can_correction='false')
                        ficha-resource(:expence_unit_id='currentFicha.estimate_unit_id', :fichaid='currentFicha.id', :mydata='currentFicha.equipment', :headers='{module_name: $t("estimate.Equipamentos"), col_name_1: $t("estimate.Efficiency"), col_name_2:  $t("estimate.Desperdicio")}', :can_correction='false')
                        ficha-resource(:expence_unit_id='currentFicha.estimate_unit_id', :fichaid='currentFicha.id', :mydata='currentFicha.subs', :headers='{module_name: $t("estimate.Subempreitadas"), col_name_1: $t("estimate.Efficiency"), col_name_2:  $t("estimate.Correccao")}', :can_correction='false')
                section.col-12.mb-3
                    div.diga-container.p-4
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
                                                label {{ currentFicha.maodeobra.total_price }} {{ $root.current_currency.symbol }}
                                            td.align-mid(style='width:12%')
                                                label {{ currentFicha.materials.total_price }} {{ $root.current_currency.symbol }}
                                            td.align-mid(style='width:12%')
                                                label {{ currentFicha.equipment.total_price }} {{ $root.current_currency.symbol }}
                                            td.align-mid(style='width:12%')
                                                label {{ currentFicha.subs.total_price }} {{ $root.current_currency.symbol }}
                                            td.align-mid(style='width:12%')
                                                label
                                                    | {{ round10(currentFicha.maodeobra.total_price + currentFicha.materials.total_price + currentFicha.equipment.total_price + currentFicha.subs.total_price) }} {{ $root.current_currency.symbol }}
                                        tr
                                            td.align-mid(style='width:40%')
                                                | {{ $t('estimate.Percentagem') }}:
                                            td.align-mid(style='width:12%')
                                                label {{ percentage(currentFicha.maodeobra.total_price) }} %
                                            td.align-mid(style='width:12%')
                                                label {{ percentage(currentFicha.materials.total_price) }} %
                                            td.align-mid(style='width:12%')
                                                label {{ percentage(currentFicha.equipment.total_price) }} %
                                            td.align-mid(style='width:12%')
                                                label {{ percentage(currentFicha.subs.total_price) }} %
                                            td.align-mid(style='width:12%')
                                                label 100 %
                section.col-12
                    button.btn.btn-diga(v-on:click="save") {{ $t('estimate.Guardar') }}
</template>

<script>
import resource from "./../estimate/ficha_resource.vue"
import {mapGetters} from "vuex";

export default {
    data: function() {
        return {
            currentFicha: null,
            isCreating: true,
        }
    },
    components: {
        'ficha-resource': resource,
    },
    props: ['id'],
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    mounted(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.Edit_ficha');
            this.load_ficha();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.New_ficha');
            let newFicha = {
                name: '',
                maodeobra: {list: [], total_price: 0},
                materials: {list: [], total_price: 0},
                equipment: {list: [], total_price: 0},
                subs: {list: [], total_price: 0},
            };
            this.currentFicha = Object.assign({}, newFicha);
            this.isCreating = true;
        }
    },
    methods: {
        load_ficha(){
            this.$root.global_loading = true;
            this.$http.get('/api/fichas/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentFicha = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        save: function(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let err = false;
                this.currentFicha.maodeobra.list.forEach(function(e){ if (!e.selected){ err = true; } });
                this.currentFicha.materials.list.forEach(function(e){ if (!e.selected){ err = true; } });
                this.currentFicha.equipment.list.forEach(function(e){ if (!e.selected){ err = true; } });
                this.currentFicha.subs.list.forEach(function(e){ if (!e.selected){ err = true; } });
                if (err) {
                    this.$toastr.w(this.$root.$t("estimate.Need_to_select_yield"), this.$root.$t("template.Warning"));
                } else {
                    let payload = Object.assign({}, this.currentFicha);
                    payload.price = this.round10(this.currentFicha.maodeobra.total_price + this.currentFicha.materials.total_price + this.currentFicha.equipment.total_price + this.currentFicha.subs.total_price);
                    if (this.isCreating) {
                        this.$http.post('/api/fichas', payload).then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            } else {
                                this.$toastr.s(this.$root.$t("estimate.Ficha_saved"), this.$root.$t("template.Success"));
                                this.$router.push({ name: 'fichas_index' });
                            }
                        }, res => {
                            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        });
                    } else {
                        this.$http.patch('/api/fichas/' + this.currentFicha.id, payload).then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            } else {
                                this.$toastr.s(this.$root.$t("estimate.Ficha_updated"), this.$root.$t("template.Success"));
                                this.$router.push({ name: 'fichas_index' });
                            }
                        }, res => {
                            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        });
                    }
                }
            });
        },
        percentage: function(part){
            let tot = this.currentFicha.maodeobra.total_price + this.currentFicha.materials.total_price + this.currentFicha.equipment.total_price + this.currentFicha.subs.total_price;
            return tot === 0 ? 0 : this.round10(part * 100 / tot);
        },
        round10: function (num){
            return Math.round(num * 100) / 100;
        },
    },
}
</script>