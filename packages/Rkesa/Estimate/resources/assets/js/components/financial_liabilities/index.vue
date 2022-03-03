<style>

</style>

<template lang="pug">
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
            label(style="padding-left:10px;") {{$t("client.Subcontractors")}}
            select.form-control(style="display: inline-block; width: 200px; margin: 0 10px 0 0;" v-model="filters.group_id")
                option(value="") {{ $t("client.All") }}
                option(v-for="group in groups" :value="group.id") {{ group.name }}
            label {{ $t("gantt.Start_date") }}
            date-picker(style="margin-bottom:15px;", format="DD.MM.YYYY", v-model="filters.date_from", :value-type="$root.valueType", :lang="$root.locale", :first-day-of-week="$root.global_settings.first_day_of_week", :width="'20%'")
            label {{ $t("gantt.End_date") }}
            date-picker(style="margin-bottom:15px;", format="DD.MM.YYYY", v-model="filters.date_to", :value-type="$root.valueType", :lang="$root.locale", :first-day-of-week="$root.global_settings.first_day_of_week", :width="'20%'")
        
        h3 {{$t("estimate.TOTAL")}}
        div.row
            div.col-md-6
                ul.list-group
                    li.list-group-item {{$t("estimate.pay_to_subcontractor")}} : 
                        b {{parseFloat(summary.pay_to_subcontractor).toFixed(2)}}
                    li.list-group-item {{$t("estimate.payment_from_client")}} : 
                        b {{parseFloat(summary.receive_from_client).toFixed(2)}}
                    li.list-group-item {{$t("estimate.difference")}} : 
                        b {{parseFloat(summary.receive_from_client - summary.pay_to_subcontractor).toFixed(2)}}
</template>

<script>
import { mapGetters } from 'vuex';
import moment from 'moment';
import estimate_column from './custom_columns/td_estimate.vue';
import pay_to_subcontractor_column from './custom_columns/td_pay_to_subcontractor.vue';
import receive_from_client_column from './custom_columns/td_receive_from_client.vue';
import difference_column from './custom_columns/td_difference.vue';

export default {
    data: function() {
        return {
            table: {
                columns: [
                    { title: this.$root.$t("client.Subcontractors"), field: 'name', tdStyle: 'width: 75px;', sortable: true },
                    { title: this.$root.$t("estimate.Estimate"), field: 'estimate', tdComp: estimate_column, sortable: false },
                    { title: this.$root.$t("estimate.pay_to_subcontractor"), field: 'estimate', tdComp: pay_to_subcontractor_column, sortable: false },
                    { title: this.$root.$t("estimate.payment_from_client"), field: 'estimate', tdComp: receive_from_client_column, sortable: false },
                    { title: this.$root.$t("estimate.difference"), field: 'estimate', tdComp: difference_column, sortable: false }
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            filters: {
                group_id: '',
                date_from: moment().add(-100, 'days'),
                date_to: moment().add(100, 'days'),
            },
            summary: {
                receive_from_client: 0.0,
                pay_to_subcontractor: 0.0
            },
        }
    },
    props: ['offset'],
    methods: {
        getResults() {
            this.$root.global_loading = true;
            this.$http.get('/api/financial_liabilities/?date_from=' + this.filters.date_from + '&date_to=' + this.filters.date_to + 
                '&group_id=' + this.filters.group_id + '&'
                + this.$root.params(this.table.query) ).then(res => {                    
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                    this.summary = data.summary;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$root.global_loading = false;
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
    },
    watch: {
        'filters.group_id': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
        'filters.date_from': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
        'filters.date_to': function(){
            this.table.query.offset = 0;
            this.getResults();
        },
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t("client.Subcontractors");
        this.$bus.$on("get_results", this.getResults);
        this.getResults();
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    computed: {
        ...mapGetters({
            groups: 'getGroups',
            groupsById: 'getGroupsById',
        }),
    },
}
</script>