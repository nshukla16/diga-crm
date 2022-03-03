<style>
    .equipment-table .v-select .dropdown-menu{
        width: auto;
    }
</style>

<template lang="pug">
    div
        label {{ $t('project.Equipment') }}
        table.table.table-striped.equipment-table
            thead
                tr
                    th {{ $t('project.Manufacturer') }}
                    th {{ $t('project.Name_of_equipment') }}
                    th(style="width: 100px;") {{ $t('project.Size') }}
                    th(style="width: 100px;") {{ $t('project.Measure') }}
                    th(style="width: 150px;") {{ $t('project.Model') }}
                    th(style="width: 150px;") {{ $t('project.Vendor_code') }}
                    th(style="width: 140px;") {{ $t('project.Count') }}
                    th(style="width: 120px;") {{ $t('template.Actions') }}
            tbody
                tr(v-for="(equipment,i) in spec.equipment")
                    td
                        fieldset.form-group(:class="{ 'has-error': errors.has('manufacturer_id'+i) }")
                            v-select(
                                :debounce='250',
                                v-model="spec.equipment[i].manufacturer",
                                v-validate="'required'",
                                v-bind:data-vv-as="$t('project.Manufacturer').toLowerCase()",
                                :on-search='get_manufacturer_options',
                                :on-change='(val) => { manufacturer_selected(val, equipment)}',
                                :options='tmp_manufacturers',
                                label="name",
                                :placeholder="$t('project.Start_to_enter_company_name')",
                                :name="'manufacturer_id'+i",
                                :disabled="disabled")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                            span.help-block(v-show="errors.has('manufacturer_id'+i)") {{ errors.first('manufacturer_id'+i) }}
                    td
                        fieldset.form-group(:class="{ 'has-error': errors.has('equipment_id'+i) }")
                            v-select(v-if="selectable",
                                :debounce='250',
                                v-model="spec.equipment[i].name",
                                v-validate="'required'",
                                v-bind:data-vv-as="$t('project.Name_of_equipment').toLowerCase()",
                                :on-search='(search, loading) => get_equipment_options(search, loading, equipment)',
                                :on-change='(val) => { equipment_selected(val, equipment)}',
                                :options='tmp_equipment',
                                label="name",
                                :placeholder="$t('project.Start_to_enter_name')",
                                :name="'equipment_id'+i",
                                :disabled="disabled")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                                template(slot="option", slot-scope="option")
                                    span(v-if="option.is_new" style="font-style: italic;color: #797979;") {{ $t('template.Add_new') }}: {{ option.name }}
                                    div(v-else) {{ option.name }} ({{ option.size }})
                            span.help-block(v-if="selectable" v-show="errors.has('equipment_id'+i)") {{ errors.first('equipment_id'+i) }}
                            input.form-control(v-else v-model="equipment.name", :disabled="disabled")
                    td
                        input.form-control(v-model="equipment.size", :disabled="disabled")
                    td
                        select.form-control(v-model="equipment.estimate_unit_id", :disabled="disabled")
                            option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                    td
                        input.form-control(v-model="equipment.model", :disabled="disabled")
                    td
                        input.form-control(v-model="equipment.vendor_code", :disabled="disabled")
                    td
                        input.form-control(type="number" v-model="equipment.count", :disabled="disabled")
                    td
                        button.btn(v-on:click="remove_equipment(equipment)", :disabled="disabled") {{ $t('template.Remove') }}
        button.btn.btn-diga(v-on:click="add_equipment", :disabled="disabled") {{ $t('template.Add') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    inject: ['$validator'],
    props: ['spec', 'selectable', 'disabled'],
    data(){
        return {
            contract_file_loading: false,
            specification_file_loading: false,
            tmp_equipment: [],
            tmp_manufacturers: [],
            last_id: '',
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    methods: {
        add_equipment(){
            let newEquipment = {
                id: 0,
                name: '',
                size: '',
                estimate_unit_id: this.units[0].id,
                model: '',
                vendor_code: '',
                count: 0,
                equipment_id: null,
                manufacturer_id: null,
            };
            this.spec.equipment.push(newEquipment);
        },
        remove_equipment(equipment){
            if (confirm(this.$root.$t("project.Sure_remove_equipment"))){
                if (!('removed_equipment' in this.spec)){
                    this.spec.removed_equipment = [];
                }
                this.spec.removed_equipment.push(equipment.id);
                let index = this.spec.equipment.indexOf(equipment);
                this.spec.equipment.splice(index, 1);
            }
        },
        get_manufacturer_options(search, loading){
            loading(true);
            this.$http.get('/api/manufacturers?query=' + search + '&limit=100&fields=name,id').then(res => {
                this.tmp_manufacturers = res.data.rows;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                loading(false);
            })
        },
        get_equipment_options(search, loading, eq){
            loading(true);
            this.$http.get('/api/equipments?query=' + search + '&limit=100&fields=id,name,size,estimate_unit_id,model,vendor_code,manufacturer_id' + (eq.manufacturer_id ? '&manufacturer_id=' + eq.manufacturer_id : '')).then(res => {
                this.tmp_equipment = res.data.rows;
                if (res.data.rows.filter(e => e.name == search).length == 0) { // if there is no option that we need
                    this.tmp_equipment.push({
                        estimate_unit_id: this.units[0].id,
                        equipment_id: null,
                        id: 0,
                        manufacturer: eq.manufacturer,
                        manufacturer_id: eq.manufacturer_id,
                        model: "",
                        name: search,
                        size: "",
                        vendor_code: "",
                        is_new: true,
                    });
                }
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                loading(false);
            })
        },
        manufacturer_selected(val, eq){
            if (val !== null){
                if (eq.manufacturer == null){
                    eq.manufacturer = {};
                }
                Object.assign(eq.manufacturer, val);
                if (eq.manufacturer_id != val.id){
                    eq.estimate_unit_id = this.units[0].id;
                    eq.equipment_id = null;
                    eq.id = 0;
                    eq.model = "";
                    eq.name = "";
                    eq.size = "";
                    eq.vendor_code = "";
                }
                eq.manufacturer_id = val.id;
            } else {
                eq.manufacturer = null;
                eq.manufacturer_id = null;
            }
        },
        equipment_selected(val, item){
            if (val !== item && typeof val != "string") {
                if (val !== null) {
                    let old_id = item.id;
                    Object.assign(item, val);
                    item.id = old_id;
                    item.equipment_id = val.id;
                } else {
                    item.name = '';
                    item.size = '';
                    item.estimate_unit_id = this.units[0].id;
                    item.model = '';
                    item.vendor_code = '';
                    item.count = 0;
                    item.equipment_id = null;
                }
                this.tmp_equipment = [];
            }
        },
    },
}
</script>