<style>
    .referrers-table td{
        padding: 5px;
    }
</style>

<template lang="pug">
    div
        div.row
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('estimate.Estimates') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Use_special_regime_of_iva_for_portugal') }}
                                input(type="checkbox" v-model="global_settings.use_special_regime_of_iva_in_estimates" style="vertical-align: middle;margin: 0; margin-left:10px;")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Use_special_regime_of_iva_for_portugal_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('template.Unlocker_user') }}
                                select.form-control(v-model="global_settings.unlocker_user_id")
                                    option(v-for="user in users" v-text="user.name" v-bind:value="user.id")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('template.Unlocker_user_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group(:class="{ 'has-error': errors.has('estimate_number') }")
                                label.control-label {{ $t('template.Last_estimate_number') }}
                                input.form-control(v-model="global_settings.last_estimate_number" name="estimate_number" v-validate="'required'" v-bind:data-vv-as="$t('template.Last_estimate_number').toLowerCase()")
                                span.help-block(v-show="errors.has('estimate_number')") {{ errors.first('estimate_number') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('template.Last_estimate_number_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group(:class="{ 'has-error': errors.has('invoice_number') }")
                                label.control-label {{ $t('template.Last_invoice_number') }}
                                input.form-control(type="number" v-model="global_settings.invoice_increment" name="invoice_number" v-validate="'required'" v-bind:data-vv-as="$t('template.Last_invoice_number').toLowerCase()")
                                span.help-block(v-show="errors.has('invoice_number')") {{ errors.first('invoice_number') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('template.Last_invoice_number_desc') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Estimate_bottom_text') }}
                                textarea.form-control(v-model="global_settings.estimate_bottom_text")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Estimate_bottom_text_info') }}
                            br
                            | #{'<span class="page"></span>'} - {{ $t('estimate.Current_page_number') }}
                            br
                            | #{'<span class="topage"></span>'} - {{ $t('estimate.Total_page_number') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Estimate_conditions_text') }}
                                textarea.form-control(v-model="global_settings.estimate_conditions_text")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Estimate_conditions_text_info') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Show_contract_in_estimates') }}
                                input(type="checkbox" v-model="global_settings.estimate_show_contract" style="vertical-align: middle;margin: 0; margin-left:10px;")
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Show_contract_in_estimates_info') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Estimate_orientation') }}
                                select.form-control(v-model="global_settings.estimate_orientation")
                                    option(value="0") {{ $t('estimate.Portrait') }}
                                    option(value="1") {{ $t('estimate.Landscape') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Estimate_orientation_info') }}
                    h2 {{ $t('estimate.Planning') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Planning_orientation') }}
                                select.form-control(v-model="global_settings.planning_orientation")
                                    option(value="0") {{ $t('estimate.Portrait') }}
                                    option(value="1") {{ $t('estimate.Landscape') }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Planning_orientation_info') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Planning_roadmap') }}
                                select.form-control(v-model="global_settings.default_planning_roadmap_id")
                                    option(v-for="up in user_plannings" :value="up.id") {{ up.name }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Planning_roadmap_info') }}
                    div.row
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Planning_service_state') }}
                                select.form-control(v-model="global_settings.user_planning_service_state_id")
                                    option(v-for="ss in service_states" :value="ss.id") {{ ss.name }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Planning_service_state_info') }}
                    div.row(v-if="global_settings.default_planning_roadmap_id > 0 && user_plannings.length > 0")
                        div.col-12.col-sm-6
                            fieldset.form-group
                                label.control-label {{ $t('estimate.Planning_user') }}
                                select.form-control(v-model="global_settings.user_planning_user_id")
                                    option(v-for="ss in user_plannings.find(a => a.id === global_settings.default_planning_roadmap_id).users" :value="ss.id") {{ ss.group.name }}
                        div.col-12.col-sm-6.info
                            i.fa.fa-question-circle-o
                            | &nbsp;{{ $t('estimate.Planning_user_info') }}
            div.col-12.col-md-6.mb-3(v-if="units")
                div.diga-container.p-4
                    h2 {{ $t('estimate.Units') }}
                    table.referrers-table
                        tr(v-for="(unit,i) in units" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="unit.measure")
                            td
                                input.form-control(v-model="unit.hours_to_do" type="number" step="0.1" :placeholder="$t('template.Time-to-do')")
                            td
                                button.btn.red(v-on:click="remove(unit)") {{ $t('template.Remove') }}
                    button.btn.btn-diga(v-on:click="add()") {{ $t('template.Add') }}
        div.row.mb-3
            div.col-12
                div.diga-container.p-4
                    h2 {{ $t('estimate.Documents') }}
                    div.table-responsive
                        table.table.table-hover
                            thead
                                tr
                                    th(style="width: 32px;") №
                                    th {{ $t('estimate.Preview') }}
                                    th {{ $t('estimate.Name') }}
                                    th {{ $t('estimate.Default_count') }}
                                    th {{ $t('estimate.Default_checked') }}
                                    th {{ $t('estimate.Actions') }}
                            tbody
                                tr(v-for="(doc,index) in documents")
                                    td {{ index+1 }}
                                    td
                                        template(v-if="extension(doc.url) == '.pdf'")
                                            pdf(v-bind:src="doc.url" style="width:100px")
                                        template(v-else)
                                            img(v-bind:src="doc.url" style="width:100px")
                                    td
                                        a(v-bind:href="doc.url") {{ doc.name+extension(doc.url) }}
                                    td
                                        input.form-control(v-model="doc.default_count" type="text")
                                    td
                                        input.form-control(v-model="doc.default_checked" type="checkbox")
                                    td
                                        a.btn.red.btn-sm.uppercase(v-on:click="delete_attachment(doc)")
                                            i.fa.fa-trash-o  {{ $t('estimate.Delete') }}
                    vue-core-image-upload(
                        :class="['btn', 'btn-diga']",
                        @imageuploading="imageuploading",
                        @imageuploaded="imageuploaded",
                        @errorhandle="imageerror",
                        :headers="{Authorization: $root.access_token}",
                        :extensions="'.pdf'",
                        :inputAccept="'.pdf'",
                        :max-file-size="$root.max_file_size",
                        :text="$t('estimate.Upload_document')"
                        url="/api/file_upload")
                    div(v-show="loading")
                        div.loader.sm-loader
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings()" style="margin-right: 20px;") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            units: null,
            documents: null,
            global_settings: null,
            removed: [],
            loading: false,
            user_plannings: [],
        }
    },
    watch: {
        c_estimate_units(){
            this.units = JSON.parse(JSON.stringify(this.c_estimate_units));
        },
        c_global_settings(){
            this.global_settings = JSON.parse(JSON.stringify(this.c_global_settings));
        },
    },
    created(){
        this.units = JSON.parse(JSON.stringify(this.c_estimate_units));
        this.global_settings = JSON.parse(JSON.stringify(this.c_global_settings));
        this.load_estimate_documents();
        this.load_user_plannings();
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.Estimate_settings');
    },
    computed: {
        ...mapGetters({
            users: 'getUsers',
            c_estimate_units: 'getEstimateUnits',
            c_global_settings: 'getGlobalSettings',
            service_states: 'getServiceStates',
        }),
    },
    methods: {
        load_user_plannings(){
            this.$http.get('/api/user_plannings').then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.user_plannings = data.rows;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        load_estimate_documents(){
            this.$root.global_loading = true;
            this.$http.get('/api/estimate_documents/').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.documents = res.data;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        save_settings(){
            let payload = JSON.parse(JSON.stringify(this.$data));
            this.$root.global_loading = true;
            this.$http.post('/api/settings/estimate', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                    this.removed = [];
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        remove(unit){
            if (confirm(this.$root.$t("estimate.Are_you_sure_want_to_remove_unit"))){
                this.removed.push(unit.id);
                let index = this.units.indexOf(unit);
                this.units.splice(index, 1);
            }
        },
        add(){
            let unit = {
                id: 0,
                measure: '',
            };
            this.units.push(unit);
        },
        imageuploading: function(){
            this.loading = true;
        },
        imageuploaded: function(res){
            this.loading = false;
            if (res.errcode == 0) {
                let attachment = {
                    name: res.name,
                    url: res.url,
                    default_count: 1,
                    default_checked: false,
                };
                this.documents.push(attachment);
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror(e){
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
        delete_attachment: function(at){
            if (confirm(this.$root.$t("estimate.Are_you_sure_want_to_delete_this"))) {
                let i = this.documents.indexOf(at);
                this.documents.splice(i, 1);
            }
        },
        extension: function(str){
            return str.substring(str.lastIndexOf('.'));
        },
    },
}
</script>