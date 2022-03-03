<style>

</style>

<template lang="pug">
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
</template>

<script>
import kpi_column from './custom_columns/td_kpi.vue';
import users_column from './custom_columns/td_users.vue';

export default {
    data: function() {
        return {
            table: {
                columns: [
                    { title: this.$root.$t("hr.Name"), field: 'name', tdStyle: 'width: 200px;', sortable: true },
                    { title: this.$root.$t("hr.Users"), tdComp: users_column },
                    { title: this.$root.$t("hr.Kpi"), tdComp: kpi_column, tdStyle: 'width: 100px;', visible: this.$root.module_enabled('kpi') && this.$root.user.can_see_kpi },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
        }
    },
    props: ['offset'],
    methods: {
        getResults() {
            this.$http.get('/api/hr/groups/?' + this.$root.params(this.table.query)).then(res => {
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
            handler (query) {
                this.getResults();
            },
            deep: true,
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.All_groups');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
}
</script>