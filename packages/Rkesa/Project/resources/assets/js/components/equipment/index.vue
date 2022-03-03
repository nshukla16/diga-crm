<template lang="pug">
    div
        h2 {{ $t('project.Equipment') }}
        section.diga-container.p-4
            div.float-sm-right.mr-2
                router-link.btn.btn-diga(style="height:38px;margin: 0 10px 0;", :to="{name: 'equipment_create'}") {{ $t('project.Equipment_new') }}
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")
</template>

<script>
import action_buttons from './custom_columns/td_edit_delete.vue';
import manufacturer_column from './custom_columns/td_manufacturer.vue';
import unit_column from './custom_columns/td_unit.vue';

export default {
    props: ['offset'],
    data(){
        return {
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: true },
                    { title: this.$root.$t('project.Name'), field: 'name', sortable: true },
                    { title: this.$root.$t('project.Measure'), tdComp: unit_column },
                    { title: this.$root.$t('project.Manufacturer'), tdComp: manufacturer_column },
                    { title: this.$root.$t('project.Size'), field: 'size', sortable: true },
                    { title: this.$root.$t('project.Model'), field: 'model', sortable: true },
                    { title: this.$root.$t('project.Vendor_code'), field: 'vendor_code', sortable: true },
                    { title: this.$root.$t('estimate.Actions'), tdClass: 'column_autosize', tdComp: action_buttons},
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('project.Equipment');
        this.$bus.$on('get_results', this.getResults);
    },
    beforeDestroy(){
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/equipments?' + this.$root.params(this.table.query)).then(res => {
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