<template lang="pug">
    div
        h2 {{ $t('project.Technical_documentation') }}
        section.diga-container.p-4
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")
</template>

<script>
import date_of_receiving_column from './custom_columns/td_date_of_receiving.vue';
import date_of_sending_column from './custom_columns/td_date_of_sending.vue';
import date_of_translation_column from './custom_columns/td_date_of_translation.vue';
import manufacturer_column from './custom_columns/td_manufacturer.vue';
import document_column from './custom_columns/td_document.vue';
import original_document_column from './custom_columns/td_original_document.vue';
import project_column from './custom_columns/td_project.vue';

export default {
    props: ['offset'],
    data(){
        return {
            table: {
                columns: [
                    { title: this.$root.$t('project.Document_name'), field: 'name', sortable: true },
                    { title: this.$root.$t('project.Project'), tdComp: project_column, field: 'project_id', sortable: true },                    
                    { title: this.$root.$t('project.Days_count_from_prepayment'), field: 'days_from_prepayment_customer', visible: false },
                    { title: this.$root.$t('project.Date_customer_td'), field: 'customer_date' },
                    { title: this.$root.$t('project.Document_language'), field: 'document_language', sortable: true, visible: false },
                    { title: this.$root.$t('project.Manufacturer'), tdComp: manufacturer_column, field: 'manufacturer_id', sortable: true },
                    { title: this.$root.$t('project.Days_count_from_prepayment') + ' (' + this.$root.$t('project.For_contract_with_manufacturer') + ')', field: 'days_from_prepayment_manufacturer', visible: false },
                    { title: this.$root.$t('project.Date_manufacturer_td'), field: 'manufacturer_date'},
                    { title: this.$root.$t('project.Availability'), field: 'available', visible: false },
                    { title: this.$root.$t('project.Date_of_receiving'), tdComp: date_of_receiving_column, field: 'receiving_date', sortable: true },
                    { title: this.$root.$t('project.Date_of_sending'), tdComp: date_of_sending_column, field: 'sending_date', sortable: true },
                    { title: this.$root.$t('project.Original_document'), tdComp: original_document_column, field: 'orig_document_file' },
                    { title: this.$root.$t('project.Date_of_translation'), tdComp: date_of_translation_column, field: 'translating_date', visible: false },
                    { title: this.$root.$t('project.Document'), tdComp: document_column, field: 'document_file', visible: false },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    created() {
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Technical_documentation');
    },
    methods: {
        getResults() {
            this.$http.get('/api/technical_documents?' + this.$root.params(this.table.query)).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
    },
    watch: {
        'table.query': {
            handler(query) {
                this.getResults();
            },
            deep: true,
        },
    },
}
</script>