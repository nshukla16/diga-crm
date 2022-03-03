<style>
    .g-core-image-upload-btn.disabled{
        pointer-events: none;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Documents') }}
        div.row
            div.col-12.col-lg-9
                div.row
                    label.col-12.col-sm-6.input-line {{ $t('project.Ready_notification') }}
                    div.col-12.col-sm-6.text-nowrap
                        div.d-inline-block(v-on:click="toogle_ready_notification()")
                            bootstrap-toggle(v-model="project.ready_notification", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        date-picker.mx-2(:disabled="!project.ready_notification || project.finished || !$root.can_with_project('update', 3)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="project.ready_notification_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="project.ready_notification_file_path"
                            :file_name="project.ready_notification_file_name"
                            :editable="project.ready_notification && !project.finished && $root.can_with_project('update', 3)"
                            @remove="remove_ready_notification"
                            @finished="(arr) => { [project.ready_notification_file_path, project.ready_notification_file_name] = arr }")
                        a.clickable.clickable-link.ml-2(v-if="legal_entities_by_id[template_legal_entity_id].ready_notification_template_file_path" v-on:click="download_notification_template") {{ $t('project.Download') }}
                div.row
                    label.col-12.col-sm-6.input-line {{ $t('project.Acceptance_certificate') }}
                    div.col-12.col-sm-6.text-nowrap
                        div.d-inline-block(v-on:click="toogle_acceptance_certificate()")
                            bootstrap-toggle(v-model="project.acceptance_certificate", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        date-picker.mx-2(:disabled="!project.acceptance_certificate || project.finished || !$root.can_with_project('update', 3)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="project.acceptance_certificate_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="project.acceptance_certificate_file_path"
                            :file_name="project.acceptance_certificate_file_name"
                            :editable="project.acceptance_certificate && !project.finished && $root.can_with_project('update', 3)"
                            @remove="remove_acceptance_certificate"
                            @finished="(arr) => { [project.acceptance_certificate_file_path, project.acceptance_certificate_file_name] = arr }")
                div.row
                    label.col-12.col-sm-6.input-line {{ $t('project.Shipping_documents_sent') }}
                    div.col-12.col-sm-6.text-nowrap
                        div.d-inline-block(v-on:click="toogle_shipping_documents_sent()")
                            bootstrap-toggle(v-model="project.shipping_documents_sent", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        date-picker.mx-2(:disabled="!project.shipping_documents_sent || project.finished || !$root.can_with_project('update', 3)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="project.shipping_documents_sent_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="project.shipping_documents_sent_file_path"
                            :file_name="project.shipping_documents_sent_file_name"
                            :editable="project.shipping_documents_sent && !project.finished && $root.can_with_project('update', 3)"
                            @remove="remove_shipping_documents_sent"
                            @finished="(arr) => { [project.shipping_documents_sent_file_path, project.shipping_documents_sent_file_name] = arr }")
                div.row
                    label.col-12.col-sm-6.input-line {{ $t('project.Shipping_documents_received') }}
                    div.col-12.col-sm-6.text-nowrap
                        div.d-inline-block(v-on:click="toogle_shipping_documents_received()")
                            bootstrap-toggle(v-model="project.shipping_documents_received", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        date-picker.mx-2(:disabled="!project.shipping_documents_received || project.finished || !$root.can_with_project('update', 3)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="project.shipping_documents_received_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="project.shipping_documents_received_file_path"
                            :file_name="project.shipping_documents_received_file_name"
                            :editable="project.shipping_documents_received && !project.finished && $root.can_with_project('update', 3)"
                            @remove="remove_shipping_documents_received"
                            @finished="(arr) => { [project.shipping_documents_received_file_path, project.shipping_documents_received_file_name] = arr }")
                div
                    div.row.mb-2(v-for="doc in project.additional_documents")
                        div.col-12.col-sm-6.input-line
                            input.form-control(v-model="doc.document_name", :disabled="project.finished || !$root.can_with_project('update', 3)")
                        div.col-12.col-sm-6.text-nowrap
                            div.d-inline-block(v-on:click="toogle_document(doc)")
                                bootstrap-toggle(v-model="doc.exist", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            date-picker.mx-2(:disabled="!doc.exist || project.finished || !$root.can_with_project('update', 3)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="doc.document_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                            file-uploader(
                                :file_url="doc.document_file"
                                :file_name="doc.document_file_name"
                                :editable="doc.exist && !project.finished && $root.can_with_project('update', 3)"
                                @remove="remove_document_file(doc)"
                                @finished="(arr) => { [doc.document_file, doc.document_file_name] = arr }")
                            button.btn.btn-danger.ml-2(v-on:click="remove_addtitional_doc(doc)" style="height: 34px;width:34px;padding: 2px;", :disabled="project.finished || !$root.can_with_project('update', 3)")
                                i.fa.fa-times
                button.btn.btn-diga(v-on:click="add_additional_document()", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('template.Add') }}
            div.col-12.col-lg-3
                label {{ $t('project.Comment') }}
                textarea.form-control(v-model="project.comment_documents", :disabled="project.finished || !$root.can_with_project('update', 3)")
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
        add_additional_document(){
            let doc = {
                id: 0,
                exist: false,
                document_date: null,
                document_file: null,
                document_file_name: null,
            };
            this.project.additional_documents.push(doc);
        },
        toogle_document(doc){
            if (!doc.exist && doc.document_date == null && !this.project.finished){
                doc.document_date = moment();
            }
        },
        remove_document_file(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                doc.document_file = null;
                doc.document_file_name = null;
            }
        },
        remove_addtitional_doc(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                if (!('removed_add_docs' in this.project)){
                    this.project.removed_add_docs = [];
                }
                this.project.removed_add_docs.push(doc.id);
                let index = this.project.additional_documents.indexOf(doc);
                this.project.additional_documents.splice(index, 1);
            }
        },
        download_notification_template(){
            this.$root.global_loading = true;
            this.$http.get('/api/project/template?type=ready_notification&id=' + this.project.id, {responseType: 'blob'}).then(response => {
                const url = window.URL.createObjectURL(new Blob([response.data]));
                const link = document.createElement('a');
                link.href = url;
                link.setAttribute('download', 'ready-notification-' + moment().format("DD-MM-YYYY-HH-mm-ss") + '.docx');
                document.body.appendChild(link);
                link.click();
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        toogle_ready_notification(){
            if (!this.project.ready_notification && this.project.ready_notification_date == null && !this.project.finished){
                this.project.ready_notification_date = moment();
            }
        },
        toogle_acceptance_certificate(){
            if (!this.project.acceptance_certificate && this.project.acceptance_certificate_date == null && !this.project.finished){
                this.project.acceptance_certificate_date = moment();
            }
        },
        toogle_shipping_documents_sent(){
            if (!this.project.shipping_documents_sent && this.project.shipping_documents_sent_date == null && !this.project.finished){
                this.project.shipping_documents_sent_date = moment();
            }
        },
        toogle_shipping_documents_received(){
            if (!this.project.shipping_documents_received && this.project.shipping_documents_received_date == null && !this.project.finished){
                this.project.shipping_documents_received_date = moment();
            }
        },
        remove_ready_notification(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.project.ready_notification_file_path = null;
                this.project.ready_notification_file_name = null;
            }
        },
        //
        remove_acceptance_certificate(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.project.acceptance_certificate_file_path = null;
                this.project.acceptance_certificate_file_name = null;
            }
        },
        //
        remove_shipping_documents_sent(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.project.shipping_documents_sent_file_path = null;
                this.project.shipping_documents_sent_file_name = null;
            }
        },
        //
        remove_shipping_documents_received(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.project.shipping_documents_received_file_path = null;
                this.project.shipping_documents_received_file_name = null;
            }
        },
    },
}
</script>