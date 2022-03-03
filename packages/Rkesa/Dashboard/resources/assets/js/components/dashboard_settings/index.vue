<style>

</style>

<template lang="pug">
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
            router-link.btn.btn-diga.pull-right(:to="{name: 'dashboard_create'}", style="margin: 0 10px 0;") {{ $t('dashboard.create_title') }}
</template>

<script>
import name_column from './custom_columns/td_name'
import actions_column from './custom_columns/td_actions'

export default {
    props: ['offset'],
    data: function () {
        return {
            table: {
                columns: [
                    { title: this.$root.$t('dashboard.name'), tdComp: name_column },
                    { title: this.$root.$t('dashboard.visible_statuses'), field: 'entities_count' },
                    { title: this.$root.$t('dashboard.widgets_count'), field: 'widgets_count' },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: actions_column },
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
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('dashboard.index_title');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/dashboards/?' + this.$root.params(this.table.query)).then(res => {
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