<style>
    .widget_wrapper{
        min-height:310px;
        margin-bottom:30px;
    }
    .chart-title{
        text-transform: uppercase;
        text-align: center;
        font-size: 18px;
        padding: 5px 20px 0;
    }
    .no-enough-data-table{
        top: 0;
        position: absolute;
        left: 0;
    }
</style>

<template lang="pug">
    div.widget_wrapper(:class="[widget.size == 1 ? 'col-12 col-md-6 col-xl-3' : 'col-12 col-md-12 col-xl-6']")
        div.widget.diga-container
            a(style="cursor:grab;")
                i.fa.fa-thumb-tack(aria-hidden="true")
            bar-chart(v-if="widget.widget_type == 1", :widget="widget")
            line-chart(v-if="widget.widget_type == 2", :widget="widget", v-on:popup_line="popup_line")
            pie-chart(v-if="widget.widget_type == 3", :widget="widget")
            table-chart(v-if="widget.widget_type == 4", :widget="widget", v-on:popup_table="popup_table")
            funnel(v-if="widget.widget_type == 5 && widget.data_type == 9", :widget="widget")
            regionalsales(v-if="widget.widget_type == 5 && widget.data_type == 10", :widget="widget")
</template>

<script>

import barChart from './charts/bar.stage.chart.vue';
import lineChart from './charts/line.stage.chart.vue';
import pieChart from './charts/pie.stage.chart.vue';
import tableChart from './charts/table.stage.vue';
import funnel from './charts/funnel.stage.vue';
import regionalsales from './charts/regional-sales.stage.vue';

export default {
    props: ['widget'],
    components: {
        barChart, lineChart, pieChart, tableChart, funnel, regionalsales,
    },
    methods: {
        popup_table(rows, header, colors){
            this.$emit('popup_table', rows, header, colors);
        },
        popup_line(rows, header){
            this.$emit('popup_line', rows, header)
        },
    },
}
</script>