<style>
    .signs td{
        font-size: 12px;
    }
    .table-widget td{
        padding: 5px;
    }
</style>

<template lang="pug">
    div.table-responsive
        table.table.table-bordered.table-striped.table-widget
            thead
                tr
                    td #
                    td {{ $t('dashboard.master_number') }}
                    td {{ $t('dashboard.task_responsible') }}
                    td {{ $t('dashboard.status_time') }}
                    td >>
            tbody(style="color: white;")
                tr(v-for="(row, index) in rows", :style="{'background-color': get_color_from_number(row.interval)}")
                    td {{ index + 1 }}
                    td {{ row.estimate_number }}
                    td {{ row.responsible }}
                    td {{ row.interval }}
                    td
                        a(href="#" v-on:click="open_client_page(row.client_contact_id)") >>
            tfoot.signs
                tr
                    td(colspan="5" style="padding: 0;")
                        table(style="width:100%; table-layout: fixed; text-align: center;")
                            tbody
                                tr
                                    td(colspan="4" style="text-align: center") {{ $t('dashboard.designations') }}
                                tr
                                    td(:style="{'background-color': get_color_from_number(1)}")
                                    td X < 7
                                    td(:style="{'background-color': get_color_from_number(8)}")
                                    td 7 <= X < 14
                                tr
                                    td(:style="{'background-color': get_color_from_number(15)}")
                                    td 14 <= X < 21
                                    td(:style="{'background-color': get_color_from_number(22)}")
                                    td 21 <= X < 28
                                tr
                                    td(:style="{'background-color': get_color_from_number(29)}")
                                    td 28 <= X
</template>

<script>
export default {
    props: ['rows', 'colors'],
    data() {
        return {}
    },
    methods: {
        open_client_page(client_id){
            $('#modal-full-list-table').modal('hide');
            this.$router.push({name: 'client_show', params: {id: client_id}});
        },
        get_color_from_number(number){
            if (number < 7){
                return '#939393';
            } else if (number >= 7 && number < 14){
                return this.colors.color1;
            } else if (number >= 14 && number < 21){
                return this.colors.color2;
            } else if (number >= 21 && number < 28){
                return this.colors.color3;
            } else {
                return this.colors.color4;
            }
        },
    },
}
</script>