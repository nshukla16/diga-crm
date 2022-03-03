<style>
    .tabs_container{
        display: grid;
        grid-template-columns: repeat(2,50%);
        margin-top: 25px;
    }
    .tabs_container > div {
        text-align: center;
        background-color: #24C5C3;
        padding: 10px 0;
        box-shadow: 0 -2px 10px rgba(0,0,0,0.5);
        transition: all .1s;
        color: #FFF;
        cursor: pointer;
    }
    .tabs_container > div:first-child {
        border-top-left-radius: 10px;
    }
    .tabs_container > div:last-child {
        border-top-right-radius: 10px;
    }
    .tabs_container > div:hover {
        background-color: #25b5c5;
    }
    .diga-container-dynamic{
        border-top-left-radius: 0;
        border-top-right-radius: 0;
    }
    div .tab_active{
        background-color: #FFF !important;
        color: #2A6668;
    }
</style>
<template lang="pug">
div
    div.tabs_container
        div(v-on:click="setActive('first')", :class="{ tab_active: isActive('first') }") {{ $t('hr.Users') }}
        div(v-on:click="setActive('second')", :class="{ tab_active: isActive('second') }") {{ $t('hr.Groups') }}
    section.diga-container.p-4.diga-container-dynamic(style="" )
        datatable.datatable-wrapper(v-bind="table")
            router-link.btn.btn-diga(style="height:38px;margin: 0 10px 0;" v-if="", :to="{ name: 'users_and_groups_create' }") {{ $t('hr.UsersAndGroups_new') }}
</template>

<script>
import name_column from './custom_columns/td_name.vue';
import period_column from './custom_columns/td_period.vue';
import type_list_column from './custom_columns/td_type_list.vue';
import actions_column from './custom_columns/td_actions.vue';

export default {
    data() {
        return {
            activeItem: 'first',
            table: {
                columns: [
                    { title: this.$root.$t("hr.Name"), tdComp: name_column, field: 'user_id', sortable: true },
                    { title: this.$root.$t("hr.KPI_DateStart"), field: 'start_date', sortable: true },
                    { title: this.$root.$t("hr.KPI_Period"), tdComp: period_column, field: 'period_id', sortable: true },
                    { title: this.$root.$t("hr.KPI_List"), tdComp: type_list_column },
                    { title: this.$root.$t('template.Actions'), tdClass: 'column_autosize', tdComp: actions_column },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {
                active: 2,
            },
        };
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
    },
    props: ['offset'],
    methods:{
        getResults() {
            this.$root.global_loading = true;
            this.$http.get('/api/kpi/users_and_groups?isGroup=' + (this.activeItem==='second') + '&' + this.$root.params(this.table.query)).then(res => {
                return res.json();                
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        isActive: function (menuItem) {
            return this.activeItem === menuItem
        },
        setActive: function (menuItem) {
            this.activeItem = menuItem;
            this.getResults();
        },

    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.kpi');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
}
</script>
