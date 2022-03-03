<style>

</style>

<template lang="pug">
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
            router-link.btn.btn-diga(:to="{name: 'checklist_create'}" style="height: 38px;margin: 0 10px 0;") {{ $t("calendar.New_checklist") }}
</template>

<script>
import title_column from './custom_columns/td_title.vue';
import actions_column from './custom_columns/td_actions.vue';

export default {
    data: function() {
        return {
            table: {
                columns: [
                    { title: this.$root.$t('calendar.Title'), field: 'name', tdComp: title_column, sortable: true },
                    { title: this.$root.$t('calendar.Questions_count'), field: 'checklist_entities_count' },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: actions_column, tdStyle: 'width: 260px;' },
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
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('calendar.All_checklists');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        getResults() {
            this.$http.get('/api/checklists/?' + this.$root.params(this.table.query)).then(res => {
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