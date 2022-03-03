<template lang="pug">
div
    h2 {{ $t('template.All_calls') }}
    section.diga-container.p-4
        datatable.datatable-wrapper(v-bind="table")
            input.form-control(v-model="query", type="text", :placeholder="$t('client.Search')" style="height:38px;min-width: 150px;display: inline-block;flex:1;margin: 0 10px 0;")
</template>

<script>

import record_column from './custom_columns/td_call_record.vue';
import duration_column from './custom_columns/td_call_duration.vue';
import type_column from './custom_columns/td_call_type.vue';

export default {
    data: function(){
        return {
            query: '',
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: true },
                    { title: this.$root.$t('client.Phone_number'), field: 'caller_id', sortable: true },
                    { title: this.$root.$t('client.Call_type'), field: 'event', tdComp: type_column, sortable: true },
                    { title: this.$root.$t('client.Call_status'), field: 'disposition', sortable: true },
                    { title: this.$root.$t('client.Call_start'), field: 'call_start', sortable: true },
                    { title: this.$root.$t('client.Call_duration'), field: 'duration', tdComp: duration_column, sortable: true },
                    { title: this.$root.$t('client.Caller_did'), field: 'called_did', sortable: true },
                    { title: this.$root.$t('client.Call_record'), field: 'record_link', tdComp: record_column, sortable: false },
                ],
                data: [],
                total: 0,
                query: {},
            },
        }
    },
    props: ['offset'],
    methods: {
        getResults() {
            this.$http.get('/api/calls?' + this.$root.params(this.table.query) + (this.query != '' ? '&query=' + this.query : '')).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    data.rows.forEach(r => {
                        r.disposition = this.$root.$t('client.Call_disposition_' + r.disposition.replace(' ', '').replace(',', ''));
                    });
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
        query: function(){
            this.table.query.offset = 0;
            this.getResults();
        },
    },
    mounted(){
        this.$bus.$on("get_results", this.getResults);
    },
}
</script>
