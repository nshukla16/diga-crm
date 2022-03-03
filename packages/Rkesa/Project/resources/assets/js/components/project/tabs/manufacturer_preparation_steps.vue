<template lang="pug">
    div
        div.project-section {{ $t('project.Stages_of_preparation_for_shipment') }}
        div.row
            div.col-12.col-lg-4
                div.row
                    label.col-12.col-sm-7.input-line {{ $t('project.Designated_shipping_date') }}
                    div.col-12.col-sm-5.text-nowrap
                        date-picker.mx-2(:lang="$root.locale" v-model="manufacturer.designated_shipping_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140", :disabled="project.finished || !$root.can_with_project('update', 1)")
                div.row
                    label.col-12.col-sm-7.input-line {{ $t('project.Fact_shipping_date') }}
                    div.col-12.col-sm-5.text-nowrap
                        date-picker.mx-2(:lang="$root.locale" v-model="manufacturer.fact_shipping_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140", :disabled="project.finished || !$root.can_with_project('update', 1)")
                div.row
                    label.col-12.col-sm-7.input-line(v-if="manufacturer.order != null") {{ $t('project.Manufacturer_order') }}
                    div.col-12.col-sm-7(v-else)
                        button.btn.btn-diga(v-on:click="create_order(manufacturer)", :disabled="project.finished || !$root.can_with_project('update', 1) || !$root.can_do('shipping_orders', 'create')") {{ $t('project.Create_shipping_order') }}
                    div.col-12.col-sm-5.text-nowrap
                        date-picker.mx-2(:disabled="manufacturer.order != null || project.finished || !$root.can_with_project('update', 1) || !$root.can_do('shipping_orders', 'create')", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="manufacturer.order_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")

            div.col-12.col-lg-5
                div.row.mb-2(v-for="doc in manufacturer.additional_documents")
                    div.col-12.col-sm-4.input-line
                        input.form-control(v-model="doc.document_name", :disabled="project.finished || !$root.can_with_project('update', 1)")
                    div.col-12.col-sm-8.text-nowrap
                        div.d-inline-block(v-on:click="toogle_document(doc)")
                            bootstrap-toggle(v-model="doc.exist", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 1)")
                        date-picker.mx-2(:disabled="!doc.exist || project.finished || !$root.can_with_project('update', 1)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="doc.document_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                        file-uploader(
                            :file_url="doc.document_file"
                            :file_name="doc.document_file_name"
                            :editable="doc.exist && !project.finished && $root.can_with_project('update', 1)"
                            @remove="remove_document_file(doc)"
                            @finished="(arr) => { [doc.document_file, doc.document_file_name] = arr }")
                        button.btn.btn-danger.ml-2(v-on:click="remove_additional_doc(doc)" style="height: 34px;width:34px;padding: 2px;", :disabled="project.finished || !$root.can_with_project('update', 1)")
                            i.fa.fa-times
                button.btn.btn-diga(v-on:click="add_additional_document()", :disabled="project.finished || !$root.can_with_project('update', 1)") {{ $t('project.Add_document') }}
            div.col-12.col-lg-3
                label {{ $t('project.Comment') }}
                textarea.form-control(v-model="manufacturer.comment_preparation_steps", :disabled="project.finished || !$root.can_with_project('update', 1)")
</template>

<script>
import moment from 'moment';

export default {
    props: ['manufacturer', 'project'],
    data: function(){
        return {
            //
        }
    },
    methods: {
        create_order(manufacturer){
            this.$bus.$emit('create_order', manufacturer);
        },
        add_additional_document(){
            let doc = {
                id: 0,
                exist: false,
                document_date: null,
                document_file: null,
                document_file_name: null,
            };
            this.manufacturer.additional_documents.push(doc);
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
                if (!('removed_add_docs' in this.manufacturer)){
                    this.manufacturer.removed_add_docs = [];
                }
                this.manufacturer.removed_add_docs.push(doc.id);
                let index = this.manufacturer.additional_documents.indexOf(doc);
                this.manufacturer.additional_documents.splice(index, 1);
            }
        },
    },
}
</script>