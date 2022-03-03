<template lang="pug">
    div
        div.project-section {{ $t('project.Documents') }}
        div.row.mb-2(v-for="(doc,i) in project.installation_documents")
            div.col-12.col-sm-4.input-line
                input.form-control(v-model="doc.document_name", :disabled="project.finished || !$root.can_with_project('update', 4)")
            div.col-12.col-sm-8.text-nowrap
                div.d-inline-block(v-on:click="toogle_document(doc)")
                    bootstrap-toggle(v-model="doc.exist", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 4)")
                date-picker.mx-2(:disabled="!doc.exist || project.finished || !$root.can_with_project('update', 4)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="doc.document_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                file-uploader(
                    :file_url="doc.document_file"
                    :file_name="doc.document_file_name"
                    :editable="!project.finished && $root.can_with_project('update', 4)"
                    @remove="remove_document_file(doc)"
                    @finished="(arr) => { [doc.document_file, doc.document_file_name] = arr }")
                button.btn.btn-danger.ml-2(v-on:click="remove_additional_doc(doc)" style="height: 34px;width:34px;padding: 2px;", :disabled="project.finished || !$root.can_with_project('update', 4)")
                    i.fa.fa-times
        button.btn.btn-diga(v-on:click="add_additional_document()", :disabled="project.finished || !$root.can_with_project('update', 4)") {{ $t('project.Add_document') }}
</template>

<script>
import moment from 'moment';

export default {
    props: ['project'],
    data(){
        return {
            //
        }
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
            this.project.installation_documents.push(doc);
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
        remove_additional_doc(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                if (!('removed_installation_add_docs' in this.project)){
                    this.project.removed_installation_add_docs = [];
                }
                this.project.removed_installation_add_docs.push(doc.id);
                let index = this.project.installation_documents.indexOf(doc);
                this.project.installation_documents.splice(index, 1);
            }
        },
    },
}
</script>