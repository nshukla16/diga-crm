<style>
    #equipment-list{

    }
</style>

<template lang="pug">
    div
        #delivery_block.diga-container(style="height: 550px;")
            .portlet-title
                .caption
                    a.btn(v-on:click="new_calculation()")
                        i.fa.fa-plus
                    card-menu(:current_section="current_section")
            .portlet-body
                div(v-bar="" style="height: 480px;")
                    div#delivery-scroller
                        table.table.table-hover
                            thead
                                tr
                                    td(style="width:18%; ") {{ $t("project.Name") }}
                                    td(style="width:18%; ") {{ $t("project.File") }}
                                    td(style="width:18%; ") {{ $t("project.Date") }}
                            tbody
                                tr(v-if="calculations.length > 0" v-for="calculation in data")
                                    td
                                        span.clickable.clickable-link(v-on:click="edit_calculation(calculation)") {{ calculation.calculation_name }}
                                    td
                                        div(style="width: 80%;display: inline-block;vertical-align: middle;")
                                            span.clickable.clickable-link(v-on:click="download_files(calculation)") {{ calculation.calculation_file_name }}
                                    td
                                        p {{ calculation.created_at }}
                                tr(v-else)
                                    td(colspan="4" style="text-align: center;") {{ $t("client.No_calculations_yet") }}

            div.modal.fade#delModal(tabindex="-1" role="dialog" aria-hidden="true")
                div.modal-dialog(role="document")
                    div.modal-content(v-if="temp_calc")
                        div.modal-header
                            h5.modal-title {{ !in_edit_mode ? $t("client.Add_delivery") : $t("client.Edit_delivery") }}
                            button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_equipment()")
                                span(aria-hidden="true") &times;
                        div.modal-body
                            fieldset.form-group(:class="{ 'has-error': errors.has('temp_calc.calculation_name') }")
                                label {{ $t('project.Name') }}
                                input.form-control(type="text", v-model="temp_calc.calculation_name" v-validate="'required'" v-bind:data-vv-as="$t('project.Name').toLowerCase()" name="temp_calc.calculation_name")
                                div.help-block(v-show="errors.has('temp_calc.calculation_name')") {{ errors.first('temp_calc.calculation_name') }}
                            file-uploader(
                                :file_url="temp_calc.calculation_file_path"
                                :file_name="temp_calc.calculation_file_name"
                                :editable="true"
                                @remove="remove_calculation_file"
                                @finished="(arr) => { [temp_calc.calculation_file_path, temp_calc.calculation_file_name] = arr }")
                        div.modal-footer(style="justify-content: space-between;")
                            button.btn.btn-secondary(v-on:click="cancel_equipment()") {{ $t("template.Cancel") }}
                            div(v-if="!in_edit_mode")
                                button.btn.btn-diga.float-right(v-on:click="save_calculations") {{ $t("template.Save") }}
                            div(v-else)
                                button.btn.btn-diga.float-right(v-on:click="save_edit_calculation") {{ $t("template.Save") }}
                                button.mr-2.btn.btn-danger.float-right(v-on:click="delete_calculation") {{ $t("template.Remove") }}
                            div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                                div.loader.sm-loader
</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';
import CardMenu from '../card_menu.vue';

export default {
    props: ['current_section', 'equipment', 'client_id', 'calculations'],
    components: { CardMenu },
    data: function () {
        return {
            loading: false,
            tmp_manufacturers: [],
            manufacturer_object: null,
            temp_calc: null,
            in_edit_mode: false,
            data: this.calculations,
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
        }),
    },
    methods: {
        new_calculation(){
            this.temp_calc = {
                calculation_file_path: null,
                calculation_file_name: null,
                calculation_name: null,
                created_at: null,
                id: null,
            };
            this.in_edit_mode = false;
            jQuery('#delModal').modal('show');
        },
        cancel_equipment(){
            this.temp_calc = {
                calculation_file_path: null,
                calculation_file_name: null,
                calculation_name: null,
                created_at: null,
                id: null,
            };
            jQuery('#delModal').modal('hide');
        },
        remove_calculation_file(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.temp_calc.calculation_file_path = null;
                this.temp_calc.calculation_file_name = null;
            }
        },
        save_calculations(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                this.$http.post('/api/calculations/', {
                    calculation_name: this.temp_calc.calculation_name,
                    calculation_file_path: this.temp_calc.calculation_file_path,
                    calculation_file_name: this.temp_calc.calculation_file_name,
                    client_id: this.client_id,
                })
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Task_finished"), this.$root.$t("template.Success"));
                            this.temp_calc.created_at = moment().format('YYYY-MM-DD HH:mm:ss');
                            this.temp_calc.id = res.data.id;
                            this.data.push(this.temp_calc);
                            event.done = true;
                            jQuery('#delModal').modal('hide');
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
            });
        },
        edit_calculation(calculation){
            this.temp_calc = {
                calculation_file_path: null,
                calculation_file_name: null,
                calculation_name: null,
                created_at: null,
                id: null,
            };
            this.in_edit_mode = true;
            this.temp_calc.calculation_name = calculation.calculation_name;
            this.temp_calc.calculation_file_name = calculation.calculation_file_name;
            this.temp_calc.calculation_file_path = calculation.calculation_file_path;
            this.temp_calc.id = calculation.id;
            jQuery('#delModal').modal('show');
        },
        save_edit_calculation(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$http.patch('/api/calculations/' + this.temp_calc.id, this.temp_calc)
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("hr.User_saved"), this.$root.$t("template.Success"));
                            let calc = this.data.find(e => e.id === this.temp_calc.id);
                            calc.calculation_name = this.temp_calc.calculation_name;
                            calc.calculation_file_name = this.temp_calc.calculation_file_name;
                            calc.calculation_file_path = this.temp_calc.calculation_file_path;
                            jQuery('#delModal').modal('hide');
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
            });
        },
        delete_calculation(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.$http.delete('/api/calculations/' + this.temp_calc.id)
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("hr.User_saved"), this.$root.$t("template.Success"));
                            this.data = this.data.filter(e => e.id !== this.temp_calc.id);
                            jQuery('#delModal').modal('hide');
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
            }
        },
        forceFileDownload(response, name){
            let link_to_format = response.url.split('.');
            let format = link_to_format.pop();
            let file_name = name + '.' + format;
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', file_name);
            document.body.appendChild(link);
            link.click();
        },
        download_files(calculation) {
            this.$http({
                method: 'get',
                url: calculation.calculation_file_path,
                responseType: 'arraybuffer',
            })
                .then(response => {
                    this.forceFileDownload(response, calculation.calculation_file_name)
                })
                .catch(() => console.log('error occurred'))
        },
    },
}
</script>