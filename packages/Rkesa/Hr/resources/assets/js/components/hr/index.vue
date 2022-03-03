<style>

</style>

<template lang="pug">
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
            input.form-control(v-model="filters.query", type="text", :placeholder="$t('template.Search')" style="height:38px;min-width: 150px;display: inline-block;flex:1;margin: 0 10px 0;")
            select.form-control(style="display: inline-block; width: 200px; margin: 0 10px 0 0;" v-model="filters.active")
                option(value="2") {{ $t("hr.All") }}
                option(value="1") {{ $t("hr.Active") }}
                option(value="0") {{ $t("hr.Inactive") }}
            router-link.btn.btn-diga(:to="{name: 'user_blank'}" style="height: 38px;margin: 0 10px 0 auto;" target="_blank") {{ $t("hr.Print_blank") }}
            router-link.btn.btn-diga(style="height:38px;margin: 0 10px 0;" v-if="$root.can_do('users', 'create') == 1", :to="{ name: 'user_create' }") {{ $t('hr.New_worker') }}
</template>

<script>
import photo_column from './custom_columns/td_photo.vue';
import name_column from './custom_columns/td_name.vue';
import active_column from './custom_columns/td_active.vue';
import actions_column from './custom_columns/td_actions.vue';
import kpi_column from './custom_columns/td_kpi.vue';
import permissions_column from './custom_columns/td_access_settings.vue';

export default {
    data: function() {
        return {
            table: {
                columns: [
                    { title: this.$root.$t("hr.Photo"), tdComp: photo_column, tdStyle: 'width: 75px;' },
                    { title: this.$root.$t("hr.Name"), field: 'name', tdComp: name_column, sortable: true },
                    { title: this.$root.$t("hr.Email"), field: 'email', sortable: true },
                    { title: this.$root.$t("hr.Kpi"), tdComp: kpi_column, visible: this.$root.module_enabled('kpi') && this.$root.user.can_see_kpi },
                    { title: this.$root.$t("hr.Status"), tdComp: active_column },
                    { title: this.$root.$t("hr.Permission_settings"), tdComp: permissions_column, visible: this.$root.user.is_admin },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: actions_column, visible: this.$root.can_do('users', 'delete') != 0 },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {
                query: '',
                active: 1,
            },
        }
    },
    props: ['offset'],
    methods: {
        getResults() {
            this.$http.get('/api/users/?fields=id,name,email,photo,active&' + this.$root.params(this.table.query) + '&active=' + this.filters.active + '&search=' + this.filters.query).then(res => {
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
        'filters.active': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
        'filters.query': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.All_users');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
}
</script>