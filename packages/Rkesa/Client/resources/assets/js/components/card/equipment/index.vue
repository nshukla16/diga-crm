<style>
</style>

<template lang="pug">
    div
        #equipment_block.diga-container(style="height: 550px;")
            .portlet-title
                .caption
                    a.btn(v-on:click="new_equipment()")
                        i.fa.fa-plus
                    card-menu(:current_section="current_section")
            .portlet-body
                div(v-bar="" style="height: 480px;")
                    div#equipment-scroller
                        div#equipment-list.pt-2.pl-2(v-if="my_equipment.length > 0")
                            div.mb-2(v-for="eq in my_equipment")
                                div(style="width: 80%;display: inline-block;vertical-align: middle;")
                                    span.clickable.clickable-link(v-on:click="open_equipment(eq)") {{ eq.name }}
                                div.date(style="width: 20%;display: inline-block;vertical-align: middle;") {{ eq.created_at }}
                        div.empty-filler(v-else) {{ $t("client.There_is_no_equipment") }}
            div.modal.fade#eqModal(tabindex="-1" role="dialog" aria-hidden="true")
                div.modal-dialog(role="document")
                    div.modal-content(v-if="currentEquipment != null")
                        div.modal-header
                            h5.modal-title {{ currentEquipment.id ? $t("client.Edit_equipment") : $t("client.New_equipment") }}
                            button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_equipment()")
                                span(aria-hidden="true") &times;
                        div.modal-body
                            fieldset.form-group
                                h6 {{ $t('project.Manufacturer') }}
                                v-select(:debounce='250',
                                    v-model="manufacturer_object",
                                    :on-search='get_manufacturer_options',
                                    :on-change='manufacturer_selected',
                                    :options='tmp_manufacturers',
                                    label="name",
                                    :placeholder="$t('project.Start_to_enter_name')")
                                    template(slot="no-options") {{ $t('template.No_matching_options') }}
                                    template(slot="option", slot-scope="option")
                                        span(v-if="option.is_new" style="font-style: italic;color: #797979;") {{ $t('template.Add_new') }}: {{ option.name }}
                                        div(v-else) {{ option.name }}
                            fieldset.form-group.mb-2(:class="{ 'has-error': errors.has('eq_name') }")
                                h6 {{ $t('project.Name_of_equipment') }}
                                input.form-control(v-model="currentEquipment.name" name="eq_name" v-validate="'required'" v-bind:data-vv-as="$t('project.Name_of_equipment').toLowerCase()")
                                span.help-block(v-show="errors.has('eq_name')") {{ errors.first('eq_name') }}
                            fieldset.mb-2
                                h6 {{ $t('project.Measure') }}
                                select.form-control(v-model="currentEquipment.estimate_unit_id")
                                    option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                            fieldset.mb-2
                                h6 {{ $t('project.Size') }}
                                input.form-control(v-model="currentEquipment.size")
                            fieldset.mb-2
                                h6 {{ $t('project.Model') }}
                                input.form-control(v-model="currentEquipment.model")
                            fieldset.mb-2
                                h6 {{ $t('project.Vendor_code') }}
                                input.form-control(v-model="currentEquipment.vendor_code")
                        div.modal-footer(style="justify-content: space-between;")
                            button.btn.grey(v-on:click="cancel_equipment()") {{ $t("template.Cancel") }}
                            //button.btn.btn-danger.float-right(v-if="event.id && $root.can_with_event('delete', event)" v-on:click="remove_event()") {{ $t("template.Remove") }}
                            button.btn.btn-diga.float-right(v-show="!loading" v-on:click="save_equipment()") {{ $t("template.Save") }}
                            div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                                div.loader.sm-loader
</template>

<script>
import {mapGetters} from "vuex";
import CardMenu from '../card_menu.vue';

export default {
    props: ['current_section', 'equipment', 'client_id'],
    components: { CardMenu },
    data: function () {
        return {
            currentEquipment: null,
            tmpEquipment: null,
            loading: false,
            tmp_manufacturers: [],
            manufacturer_object: null,
            my_equipment: this.equipment,
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    methods: {
        new_equipment(){
            let newEquipment = {
                id: null,
                client_id: this.client_id,
                name: '',
                size: '',
                model: '',
                estimate_unit_id: this.units[0].id,
                vendor_code: '',
                manufacturer_id: null,
                manufacturer_name: null,
            };
            this.manufacturer_object = null;
            this.currentEquipment = newEquipment;
            jQuery('#eqModal').modal('show');
        },
        open_equipment(eq){
            this.currentEquipment = Object.assign({}, eq);
            this.tmpEquipment = eq;
            if (eq.manufacturer_id || eq.manufacturer_name) {
                this.manufacturer_object = {
                    id: eq.manufacturer_id,
                    name: eq.manufacturer_id ? eq.manufacturer.name : eq.manufacturer_name,
                };
            } else {
                this.manufacturer_object = null;
            }
            jQuery('#eqModal').modal('show');
        },
        get_manufacturer_options(search, loading){
            loading(true);
            this.$http.get('/api/manufacturers?query=' + search + '&limit=100&fields=name,id').then(res => {
                this.tmp_manufacturers = res.data.rows;
                if (res.data.rows.filter(e => e.name == search).length == 0) { // if there is no option that we need
                    this.tmp_manufacturers.push({
                        id: null,
                        client_id: this.client_id,
                        name: search,
                        size: '',
                        model: '',
                        estimate_unit_id: this.units[0].id,
                        vendor_code: '',
                        manufacturer_id: null,
                        manufacturer_name: null,
                        is_new: true,
                    });
                }
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                loading(false);
            })
        },
        manufacturer_selected(val){
            if (val !== null){
                this.currentEquipment.manufacturer_id = val.id;
                this.currentEquipment.manufacturer_name = val.name;
                this.currentEquipment.manufacturer = val;
                this.manufacturer_object = val;
            } else {
                this.currentEquipment.manufacturer_id = null;
                this.currentEquipment.manufacturer_name = null;
                this.currentEquipment.manufacturer = null;
                this.manufacturer_object = null;
            }
        },
        cancel_equipment(){
            jQuery('#eqModal').modal('hide');
        },
        save_equipment(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.loading = true;
                if (this.currentEquipment.id == null) {
                    this.$http.post('/api/client_equipments', this.currentEquipment).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Equipment_saved"), this.$root.$t("template.Success"));
                            this.currentEquipment.id = res.data.id;
                            this.currentEquipment.created_at = res.data.created_at;
                            this.my_equipment.push(this.currentEquipment);
                            jQuery('#eqModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                } else {
                    this.$http.patch('/api/client_equipments/' + this.currentEquipment.id, this.currentEquipment).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Equipment_saved"), this.$root.$t("template.Success"));
                            let i = this.my_equipment.indexOf(this.tmpEquipment);
                            this.my_equipment[i] = this.currentEquipment;
                            jQuery('#eqModal').modal('hide');
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                }
            });
        },
    },
}
</script>