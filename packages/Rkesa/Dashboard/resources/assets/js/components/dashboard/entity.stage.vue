<style>
    .table.entity-table > thead > tr > th,
    .table.entity-table > thead > tr > td,
    .table.entity-table > tbody > tr > th,
    .table.entity-table > tbody > tr > td,
    .table.entity-table > tfoot > tr > th,
    .table.entity-table > tfoot > tr > td {
        padding: 5px;
    }
    .entity{
        display: inline-block;
        vertical-align: top;
        max-width:360px;
        min-height:330px;
        margin-right:30px;
    }
    .entity:last-child {
        margin-right: 0;
    }
    .entity-header{
        text-transform: uppercase;
        font-size: 20px;
    }
</style>

<template lang="pug">
    div.entity.diga-container
        div(style="overflow-x:hidden; padding: 0 10px 0 10px;position:relative;")
            div.color3-text.entity-header {{ entity.state.name }}
            div(style="overflow-x: scroll;")
                table.table.table-bordered.table-striped.entity-table(style="margin-bottom:0;height: 200px;")
                    thead
                        tr
                            th #
                            th(v-for="field in entity.fields", v-text="$t('dashboard.'+field.text)")
                            th >>
                    tbody
                        tr(v-if="entity.loading")
                            td(v-bind:colspan="entity.fields.length + 2" style="vertical-align: middle;text-align: center;")
                                div.loader.sm-loader
                        tr(v-else-if="rows.length != 0" v-for="(row, index) in rows")
                            td {{ index + 1 }}
                            template(v-for="(td,i) in row" v-if="i != 0")
                                td(v-if="master_sum_index == null || i != master_sum_index + 1" v-text="td")
                                td(v-else v-text="$root.format_money(td)")
                            td
                                router-link(:to="{name: 'client_show', params: {id: row[0]}}") >>
                        tr(v-else)
                            td(v-bind:colspan="entity.fields.length + 2" style="vertical-align: middle;text-align: center;")
                                span {{ $t('dashboard.No_data') }}
            div(style="position:relative;margin-top:10px;min-height: 30px;")
                table(style="width: 100%;")
                    tr
                        td
                            div
                                span(v-text="$t('dashboard.quantity') + ': '")
                                span(v-text="entity.total_rows_count")
                            div(v-if="master_sum_exist()")
                                span {{ $t('dashboard.Total') }}:
                                span {{ ' ' + $root.format_money(entity.total_master_sum) }}
                        td(style="text-align: right;")
                            button.btn-diga.btn-sm(v-text="$t('dashboard.check_all')", v-on:click="full_entity()")
</template>

<script>
export default {
    props: ['entity'],
    data() {
        return {

        }
    },

    methods: {
        full_entity() {
            this.entity.loading = true;
            this.$store.dispatch('stage/get_full_entity', { index: this.entity.service_state_id }).then(() => {
                this.entity.loading = false;
                this.$emit('full', this.entity);
            })
        },
        master_sum_exist(){
            let exist = false;
            this.entity.fields.forEach(function(el){
                if (el.text == 'master_sum'){
                    exist = true;
                }
            });
            return exist;
        },
    },
    computed: {
        rows(){
            return this.$store.getters['stage/entity_rows'](this.entity.service_state_id);
        },
        master_sum_index(){
            return this.$store.getters['stage/master_sum_index'](this.entity.service_state_id);
        },
    },
}
</script>