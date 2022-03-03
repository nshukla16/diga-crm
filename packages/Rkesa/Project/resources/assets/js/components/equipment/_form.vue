<template lang="pug">
    div
        h2 {{ isCreating ? $t('project.Equipment_new') : $t('project.Equipment_edit') }}
        section.diga-container.p-4(v-if="currentEquipment")
            table.table.table-striped
                thead
                    tr
                        th {{ $t('project.Manufacturer_name') }}
                        th {{ $t('project.Name_of_equipment') }}
                        th {{ $t('project.Measure') }}
                        th {{ $t('project.Size') }}
                        th {{ $t('project.Model') }}
                        th {{ $t('project.Vendor_code') }}
                tbody
                    tr
                        td
                            fieldset.form-group(:class="{ 'has-error': errors.has('client_id') }")
                                v-select(:debounce='250',
                                    v-model="currentEquipment.manufacturer_object",
                                    v-validate="'required'",
                                    v-bind:data-vv-as="$t('project.Buyer').toLowerCase()",
                                    :on-search='get_manufacturers_options',
                                    :on-change='company_selected',
                                    :options='tmp_manufacturers',
                                    :placeholder="$t('project.Start_to_enter_company_name')",
                                    name="client_id")
                                    template(slot="no-options") {{ $t('template.No_matching_options') }}
                        td
                            input.form-control(v-model="currentEquipment.name")
                        td
                            select.form-control(v-model="currentEquipment.estimate_unit_id")
                                option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                        td
                            input.form-control(v-model="currentEquipment.size")
                        td
                            input.form-control(v-model="currentEquipment.model")
                        td
                            input.form-control(v-model="currentEquipment.vendor_code")
            button.btn.btn-diga(v-on:click="store_equipment") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data(){
        return {
            tmp_manufacturers: [],

            currentEquipment: null,
            isCreating: true,
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    created(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Equipment_edit');
            this.load_equipment();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Equipment_new');
            let newEquipment = {
                name: '',
                size: '',
                model: '',
                vendor_code: '',
                manufacturer_id: '',
            };
            this.currentEquipment = Object.assign({}, newEquipment);
        }
    },
    methods: {
        load_equipment(){
            this.$root.global_loading = true;
            this.$http.get('/api/equipments/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentEquipment = res.data;
                    this.isCreating = false;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        get_manufacturers_options(search, loading){
            loading(true);
            this.$http.get('/api/manufacturers?query=' + search + '&limit=100&fields=id,name').then(res => {
                var processedData = [];
                res.data.rows.forEach(function(i){
                    processedData.push({'label': i.name, 'value': i.id});
                });
                this.tmp_manufacturers = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        company_selected(val){
            if (val !== null){
                this.currentEquipment.manufacturer_object = val;
                this.currentEquipment.manufacturer_id = val.value;
            } else {
                this.currentEquipment.manufacturer_object = null;
                this.currentEquipment.manufacturer_id = null;
            }
        },
        store_equipment(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/equipments', this.currentEquipment).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Equipment_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'equipments_index'});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/equipments/' + this.id, this.currentEquipment).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("project.Manufacturer_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'equipments_index'});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                }
            });
        },
    },
}
</script>