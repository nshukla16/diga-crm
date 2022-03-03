<style>
    .import-table th{
        min-width: 200px;
    }
</style>

<template lang="pug">
    div.modal.fade.modal-mail#modal-import(tabindex="-1" aria-hidden="true")
        div.modal-dialog.modal-dialog-centered(role="document", :class="{'modal-lg': step == 2}")
            div.modal-content
                div.modal-body(style="text-align: center;")
                    template(v-if="step == 1")
                        h2 {{ $t('template.Import_step_1') }}
                        div(v-html="$t('template.Import_full_desc')").mb-2
                        vue-core-image-upload(
                            :class="['btn', 'btn-diga']",
                            @imageuploading="imageuploading",
                            @imageuploaded="imageuploaded",
                            @errorhandle="imageerror",
                            :headers="{Authorization: $root.access_token}",
                            :extensions="'xlsx,xls,ods,csv'",
                            :inputAccept="'xlsx,xls,ods,csv'",
                            :max-file-size="$root.max_file_size",
                            :text="$t('estimate.Upload_document')"
                            url="/api/file_upload")
                        a.ml-3(:href="'/example_'+$root.locale+'.csv'") {{ $t('template.Import_example') }}
                        div(v-show="loading")
                            div.loader.sm-loader
                    template(v-if="step == 2")
                        h2 {{ $t('template.Import_step_2') }}
                        div.mb-2 {{ $t("template.Import_step2_desc") }}
                        div.table-responsive.mb-2
                            table.table.table-striped.import-table
                                thead
                                    tr
                                        th(v-for="column in columns") {{ column }}
                                tbody
                                    tr(v-for="row in rows")
                                        td(v-for="data in row") {{ data }}
                                tfoot
                                    tr
                                        td(v-for="i in columns.length")
                                            select.form-control(v-model="columns_values[i-1]")
                                                option(value="0") {{ $t('template.Not_import') }}
                                                optgroup(v-for="group in types", :label="group.name")
                                                    option(:value="option.value" v-for="option in group.options") {{ option.name }}
                        span.clickable.clickable-link.float-left(style="line-height: 37px;", @click="back") {{ $t('template.Back_to_step_1') }}
                        button.btn.btn-diga.float-right(@click="make_import") {{ $t('template.Import') }}
</template>

<script>
export default {
    data(){
        return {
            loading: false,
            step: 1,
            columns: [],
            columns_values: [],
            rows: [],
            types: [
                {
                    name: this.$root.$t('client.Contact'),
                    options: [
                        {value: 1, name: this.$root.$t('client.Contact_name')},
                        {value: 2, name: this.$root.$t('client.Contact_phone')},
                        {value: 3, name: this.$root.$t('client.Contact_email')},
                        {value: 4, name: this.$root.$t('client.Contact_note')},
                    ],
                },
                {
                    name: this.$root.$t('client.Company_name'),
                    options: [
                        {value: 5, name: this.$root.$t('client.Company_name')},
                        {value: 6, name: this.$root.$t('client.Company_phone')},
                        {value: 7, name: this.$root.$t('client.Company_email')},
                        {value: 8, name: this.$root.$t('client.Company_note')},
                    ],
                },
                {
                    name: this.$root.$t('client.Service'),
                    options: [
                        {value: 9, name: this.$root.$t('client.Service_name')},
                        {value: 10, name: this.$root.$t('client.Service_sum')},
                        {value: 11, name: this.$root.$t('service.Service_responsible')},
                        {value: 12, name: this.$root.$t('service.Service_state')},
                        {value: 13, name: this.$root.$t('client.Service_date')},
                        {value: 14, name: this.$root.$t('service.Service_note')},
                    ],
                },
            ],
            url: null,
            name: null,
        }
    },
    methods: {
        back(){
            this.step = 1;
        },
        load_import_settings(){
            this.$root.global_loading = true;
            this.$http.post('/api/import_settings', { src: this.url, filename: this.name }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                } else {
                    this.$root.global_loading = false;
                    this.columns_values = Array(res.data.columns.length).fill(0, 0);
                    this.columns = res.data.columns;
                    this.rows = res.data.rows;
                    this.step = 2;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        hasDuplicates(array) {
            return (new Set(array)).size !== array.length;
        },
        make_import(){
            let values_without_0 = this.columns_values.filter(function(element) {
                return element !== 0;
            });
            if (this.hasDuplicates(values_without_0)) {
                this.$toastr.w(this.$root.$t("template.Not_possible_to_have_dublicate_columns"), this.$root.$t("template.Warning"));
            } else {
                this.$root.global_loading = true;
                this.$http.post('/api/import_data', {
                    src: this.url,
                    filename: this.name,
                    values: this.columns_values,
                }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    } else {
                        this.$toastr.s(this.$root.$t("template.Import_complete"), this.$root.$t("template.Success"));
                        this.$root.global_loading = false;
                        $('#modal-import').modal('hide');
                        this.step = 1;
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            }
        },
        imageuploading() {
            this.loading = true;
        },
        imageuploaded(res) {
            this.loading = false;
            if (res.errcode == 0) {
                this.url = res.url;
                this.name = res.name;
                this.load_import_settings();
            } else {
                this.$toastr.e(res.errmess, this.$root.$t("template.Error"));
            }
        },
        imageerror(e){
            this.loading = false;
            this.$toastr.e(e, this.$root.$t("template.Error"));
        },
    },
}
</script>