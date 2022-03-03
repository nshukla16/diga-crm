<style>
    .customs_docs td{
        vertical-align: middle;
    }
</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Customs_documents') }}: {{ manufacturer.manufacturer.name }}
        div.row
            div.col-12.col-lg-6
                div.project-section {{ $t('project.Package_of_documents_required_for_customs') }}
                table.table.table-striped.customs_docs
                    thead
                        tr
                            th
                            th {{ $t('project.Name') }}
                            th(style="width: 150px;") {{ $t('project.Date') }}
                            th {{ $t('project.Document') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="doc in order.customs_documents")
                            td
                                input(type='checkbox' name='checked_doc'  @click="add_to_download(doc)")
                                <!--div(v-on:click="toogle_exist(doc)" style="width: 80px;")-->
                                <!--bootstrap-toggle(v-model="doc.exist", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 2)")-->
                            td
                                input.form-control(v-model="doc.name", :disabled="project.finished || !$root.can_with_project('update', 2)")

                            td
                                date-picker(format="DD.MM.YYYY" v-model="doc.date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 2)")
                            td
                                file-uploader(
                                    :file_url="doc.file"
                                    :file_name="doc.file_name"
                                    :editable="!project.finished && $root.can_with_project('update', 2)"
                                    @remove="remove_customs_file(doc)"
                                    @finished="(arr) => { [doc.file, doc.file_name] = arr }")
                            td
                                button.btn.btn-danger(v-on:click="remove_doc(doc)", :disabled="project.finished || !$root.can_with_project('update', 2)") {{ $t('template.Remove') }}
                button.btn.btn-diga(v-on:click="add_doc", :disabled="project.finished || !$root.can_with_project('update', 2)") {{ $t('template.Add') }}
                button.btn.btn-diga.float-right(v-if="show_download" v-on:click="download_files") {{ $t('project.Download_only_marked') }}
            div.col-12.col-lg-6
                div.project-section {{ $t('project.Customs_process') }}
                div.row
                    div.col-6
                        div.form-group.row
                            label.col-sm-5.col-form-label {{ $t('project.Date_of_application') }}
                            div.col-sm-7.d-flex
                                span(v-on:click="toogle_customs_application()" style="width: 80px;")
                                    bootstrap-toggle(v-model="order.customs_application", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 2)")
                                date-picker.ml-2(format="DD.MM.YYYY" v-model="order.customs_application_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    div.col-6
                        div.form-group.row
                            label.col-sm-5.col-form-label {{ $t('project.Date_of_issue') }}
                            div.col-sm-7.d-flex
                                span(v-on:click="toogle_customs_issue()" style="width: 80px;")
                                    bootstrap-toggle(v-model="order.customs_issue", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 2)")
                                date-picker.ml-2(format="DD.MM.YYYY" v-model="order.customs_issue_date", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 2)")
                div.row.mb-2
                    // № декрации товара
                    label.col-sm-4.col-form-label {{ $t('project.Dt') }}
                    div.col-4
                        input.form-control(v-model="order.dt", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    div.col-4.input-line
                        file-uploader(
                            :file_url="order.dt_file"
                            :file_name="order.dt_file_name"
                            :editable="!project.finished && $root.can_with_project('update', 2)"
                            @remove="remove_dt_file"
                            @finished="(arr) => { [order.dt_file, order.dt_file_name] = arr }")
                div.row.mb-2
                    label.col-sm-4.col-form-label {{ $t('project.Approximate_arrival_date_to_temporary') }}
                    div.col-4
                        date-picker(format="DD.MM.YYYY" v-model="order.approximate_date_of_arrival_to_temporary", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    div.col-4
                div.row
                    label.col-sm-4.col-form-label {{ $t('project.Approximate_arrival_date') }}
                    div.col-4
                        date-picker(format="DD.MM.YYYY" v-model="order.approximate_date_of_arrival", :first-day-of-week="$root.global_settings.first_day_of_week", :value-type="$root.valueType", :lang="$root.locale", :width="'100%'", :disabled="project.finished || !$root.can_with_project('update', 2)")
                    div.col-4
                div.row
                    label.col-sm-4.col-form-label {{ $t('project.Fact_delivery_date') }}
                    div.col-4.d-flex
                        div.d-inline-block.mr-2(v-on:click="toogle_fact_delivery()")
                            bootstrap-toggle(v-model="order.fact_delivery", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="80", data-height="36", data-onstyle="default", :disabled="project.finished || !$root.can_with_project('update', 2)")
                        date-picker(style="flex:1;", :disabled="!order.fact_delivery || project.finished || !$root.can_with_project('update', 2)", :first-day-of-week="$root.global_settings.first_day_of_week", :lang="$root.locale" v-model="order.fact_delivery_date", :value-type="$root.valueType", format="DD.MM.YYYY", :width="140")
                    div.col-4
</template>

<script>
import moment from 'moment';

export default {
    props: {
        project: Object,
        manufacturer: Object,
        order: Object,
    },
    data: function(){
        return {
            temp_link_arr: [],
        }
    },
    computed:{
        show_download(){
            if(this.temp_link_arr.length >= 1){
                return true;
            } else {
                return false;
            }
        }
    },
    methods: {
        toogle_fact_delivery(){
            if (!this.order.fact_delivery && this.order.fact_delivery_date == null && !this.project.finished){
                this.order.fact_delivery_date = moment();
            }
        },
        toogle_exist(doc){
            if (!doc.exist && doc.date == null && !this.project.finished){
                doc.date = moment();
            }
        },
        toogle_customs_application(){
            if (!this.order.customs_application && this.order.customs_application_date == null && !this.project.finished){
                this.order.customs_application_date = moment();
            }
        },
        toogle_customs_issue(){
            if (!this.order.customs_issue && this.order.customs_issue_date == null && !this.project.finished){
                this.order.customs_issue_date = moment();
            }
        },
        add_doc(){
            let newDoc = {
                id: 0,
                name: '',
                exist: false,
                date: null,
                file: null,
                file_name: null,
            };
            this.order.customs_documents.push(newDoc);
        },
        remove_doc(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))){
                if (!('removed_customs_documents' in this.order)){
                    this.order.removed_customs_documents = [];
                }
                this.order.removed_customs_documents.push(doc.id);
                let index = this.order.customs_documents.indexOf(doc);
                this.order.customs_documents.splice(index, 1);
            }
        },
        remove_customs_file(doc){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                doc.file = null;
                doc.file_name = null;
                //selected = false;
            }
        },
        remove_dt_file(){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_document"))) {
                this.order.dt_file = null;
                this.order.dt_file_name = null;
            }
        },
        download_marked(){
        },
        add_to_download(doc){
            let index = this.temp_link_arr.indexOf(doc);
            if(index === -1){
                this.temp_link_arr.push(doc);
            } else {
               this.temp_link_arr.splice(index, 1);
            }
            console.log(this.temp_link_arr);
        },
        forceFileDownload(response, name){
            let link_to_format = response.url.split('.');
            let format = link_to_format.pop();
            let file_name = name + '.' + format;
            const url = window.URL.createObjectURL(new Blob([response.data]))
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', file_name);
            document.body.appendChild(link);
            link.click();
        },
        download_files() {
            console.log("download");
            for(let i = 0; i < this.temp_link_arr.length; i++){
                if (this.temp_link_arr[i].file) {
                    this.$http({
                        method: 'get',
                        url: this.temp_link_arr[i].file,
                        responseType: 'arraybuffer',
                    })
                        .then(response => {
                            this.forceFileDownload(response, this.temp_link_arr[i].file_name)
                        })
                        .catch(() => console.log('error occured'))
                }
            }
        },
    },
}
</script>