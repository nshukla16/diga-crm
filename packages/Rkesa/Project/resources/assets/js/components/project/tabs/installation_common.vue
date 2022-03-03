<style>
    .g-core-image-upload-btn.disabled{
        pointer-events: none;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Installation_and_service') }}
        div.row
            div.col-12.col-lg-4
                fieldset.form-group
                    label {{ $t('project.Installation_duration') }}
                    input.form-control(v-model="project.installation_duration", :disabled="project.finished || !$root.can_with_project('update', 4)")
                fieldset.form-group
                    label {{ $t('project.Direct_expenses') }}
                    select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.direct_expenses", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        option(:value="0") {{ $t('project.Customer') }}
                        option(:value="1") {{ $t('project.Provider') }}
                        option(:value="2") {{ $t('project.Apart') }}
                div(v-show="project.direct_expenses == '2'")
                    fieldset.form-group
                        label {{ $t('project.Meal') }}
                        select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.food_expenses", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            option(:value="0") {{ $t('project.Customer') }}
                            option(:value="1") {{ $t('project.Provider') }}
                    fieldset.form-group
                        label {{ $t('project.Accommodation') }}
                        select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.accommodation_expenses", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            option(:value="0") {{ $t('project.Customer') }}
                            option(:value="1") {{ $t('project.Provider') }}
            div.col-12.col-lg-2
                fieldset.form-group
                    label {{ $t('project.Transportation_expenses') }}
                    select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.transportation_expenses", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        option(:value="0") {{ $t('project.Customer') }}
                        option(:value="1") {{ $t('project.Provider') }}
                fieldset.form-group
                    label {{ $t('project.Airline_tickets') }}
                    select.form-control(style="display: inline-block;min-width: 150px;flex:1;" v-model="project.airline_tickets_expenses", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        option(:value="0") {{ $t('project.Customer') }}
                        option(:value="1") {{ $t('project.Provider') }}
            div.col-12.col-lg-3
                fieldset.form-group
                    label {{ $t('project.Comment') }}
                    textarea.form-control(v-model="project.payment_installation_comment", :disabled="project.finished || !$root.can_with_project('update', 4)")
        div
            table.table.table-striped
                thead
                    tr
                        td {{ $t('project.Manufacturer') }}
                        td {{ $t('project.Installation_date') }}
                        td {{ $t('project.Equipment_commissioning_certificate') }}
                        td {{ $t('project.Equipment_commissioning_experience_certificate') }}
                        td {{ $t('project.Warranty_period') }} ({{ $t('project.In_months') }})
                        td {{ $t('project.Warranty_expiration_date') }}
                tbody
                    tr(v-for="manufacturer in project.manufacturers")
                        td {{ manufacturer.manufacturer.name }}
                        td
                            date-picker(:lang="$root.locale" v-model="manufacturer.initial_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td.column_autosize
                            div(v-on:click="toogle_equipment_certificate(manufacturer)" style="width: 80px;display:inline-block;")
                                bootstrap-toggle(v-model="manufacturer.equipment_certificate", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            date-picker.mx-2(@change="generate_warranty_expiration_date(manufacturer)", :first-day-of-week="$root.global_settings.first_day_of_week", :disabled="!manufacturer.equipment_certificate || project.finished || !$root.can_with_project('update', 4)", :lang="$root.locale" v-model="manufacturer.equipment_certificate_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                            file-uploader(
                                :file_url="manufacturer.equipment_certificate_file_path"
                                :file_name="manufacturer.equipment_certificate_file_name"
                                :editable="manufacturer.equipment_certificate && !project.finished && $root.can_with_project('update', 4)"
                                @remove="remove_equipment_certificate(manufacturer)"
                                @finished="(arr) => { [manufacturer.equipment_certificate_file_path, manufacturer.equipment_certificate_file_name] = arr }")
                            a.clickable.clickable-link.ml-2(v-if="legal_entities_by_id[template_legal_entity_id].commissioning_certificate_template_file_path" v-on:click="download_certificate_template") {{ $t('project.Download') }}
                        td.column_autosize
                            div(v-on:click="toogle_equipment_ex_certificate(manufacturer)" style="width: 80px;display:inline-block;")
                                bootstrap-toggle(v-model="manufacturer.equipment_ex_certificate", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 4)")
                            date-picker.mx-2(@change="generate_warranty_expiration_date(manufacturer)", :first-day-of-week="$root.global_settings.first_day_of_week", :disabled="!manufacturer.equipment_ex_certificate || project.finished || !$root.can_with_project('update', 4)", :lang="$root.locale" v-model="manufacturer.equipment_ex_certificate_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                            file-uploader(
                                :file_url="manufacturer.equipment_ex_certificate_file_path"
                                :file_name="manufacturer.equipment_ex_certificate_file_name"
                                :editable="manufacturer.equipment_ex_certificate && !project.finished && $root.can_with_project('update', 4)"
                                @remove="remove_equipment_ex_certificate(manufacturer)"
                                @finished="(arr) => { [manufacturer.equipment_ex_certificate_file_path, manufacturer.equipment_ex_certificate_file_name] = arr }")
                            a.clickable.clickable-link.ml-2(v-if="legal_entities_by_id[template_legal_entity_id].commissioning_experience_certificate_template_file_path" v-on:click="download_experience_certificate_template") {{ $t('project.Download') }}
                        td
                            input.form-control(@change="generate_warranty_expiration_date(manufacturer)", v-model="manufacturer.warranty_period" type="number", min="1", :disabled="project.finished || !$root.can_with_project('update', 4)")
                        td
                            date-picker(:lang="$root.locale" v-model="manufacturer.warranty_expiration_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 4)")
</template>

<script>
import moment from 'moment';
import {mapGetters} from "vuex";

export default {
    props: {
        project: {
            type: Object,
        },
    },
    data: function () {
        return {
            //
        }
    },
    mounted(){

    },
    computed: {
        ...mapGetters({
            legal_entities_by_id: 'getLegalEntitiesById',
        }),
        template_legal_entity_id(){
            if (this.project.contract_type == 0) {
                return this.project.seller_legal_entity_id;
            } else {
                return this.project.manufacturers[0].commission_relations[0].legal_entity_id;
                // return this.project.commissioner_legal_entity_id;
            }
        },
    },
    methods: {
        toogle_equipment_certificate(manufacturer){
            if (!manufacturer.equipment_certificate && manufacturer.equipment_certificate_date == null && !this.project.finished){
                manufacturer.equipment_certificate_date = moment();
                this.generate_warranty_expiration_date(manufacturer);
            }
        },
        toogle_equipment_ex_certificate(manufacturer){
            if (!manufacturer.equipment_ex_certificate && manufacturer.equipment_ex_certificate_date == null && !this.project.finished){
                manufacturer.equipment_ex_certificate_date = moment();
                this.generate_warranty_expiration_date(manufacturer);
            }
        },
        download_experience_certificate_template(){
            this.$root.global_loading = true;
            this.$http.get('/api/project/template?type=commissioning_experience_certificate&id=' + this.project.id, {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'commissioning-experience-certificate-' + moment().format("DD-MM-YYYY-HH-mm-ss") + '.docx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        download_certificate_template(){
            this.$root.global_loading = true;
            this.$http.get('/api/project/template?type=commissioning_certificate&id=' + this.project.id, {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'commissioning-certificate-' + moment().format("DD-MM-YYYY-HH-mm-ss") + '.docx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        // what is defines first (equipment_certificate_date or equipment_ex_certificate_date) + warranty_period = warranty_expiration_date
        generate_warranty_expiration_date(manufacturer){
            if (manufacturer.warranty_expiration_date == null && manufacturer.warranty_period != null){
                if (manufacturer.equipment_certificate_date == null && manufacturer.equipment_ex_certificate_date != null){
                    manufacturer.warranty_expiration_date = moment(manufacturer.equipment_ex_certificate_date).add(manufacturer.warranty_period, 'months');
                } else if (manufacturer.equipment_certificate_date != null && manufacturer.equipment_ex_certificate_date == null){
                    manufacturer.warranty_expiration_date = moment(manufacturer.equipment_certificate_date).add(manufacturer.warranty_period, 'months');
                }
            }
        },
        remove_equipment_certificate(manufacturer){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                manufacturer.equipment_certificate_file_path = null;
                manufacturer.equipment_certificate_file_name = null;
            }
        },
        //
        remove_equipment_ex_certificate(manufacturer){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                manufacturer.equipment_ex_certificate_file_path = null;
                manufacturer.equipment_ex_certificate_file_name = null;
            }
        },

    },
}
</script>