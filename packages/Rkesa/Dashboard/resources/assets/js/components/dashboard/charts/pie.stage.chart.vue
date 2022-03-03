<template lang="pug">
    section.h-100.px-2
        table.text-center.h-100.w-100(v-if="widget.loading")
            tr
                td
                    div.loader.sm-loader
        template(v-if="!widget.loading")
            div.h-100(v-if="not_enough_data")
                div.chart-title {{ title }}
                table.text-center.h-100.w-100.no-enough-data-table
                    tr
                        td
                            div(style="font-size: 50px;")
                                i.fa.fa-database
                            div {{ $t('dashboard.Not_enough_data') }}
            highcharts(v-else :options="options")
</template>

<script>
import { genComponent } from 'vue-highcharts';
import Highcharts from 'highcharts';

export default {
    props: ['widget'], // widget from vuex
    data() {
        return {

        }
    },
    components: {
        Highcharts: genComponent('Highcharts', Highcharts),
    },
    computed: {
        not_enough_data(){
            return this.widget.series.length == 0 || (this.widget.series.length == 1 && this.widget.series[0].data.every(e => e === 0));
        },
        title(){
            // return this.$store.getters['stage/getChartTitle'](this.widget.id);
            return this.$root.getChartTitle(this.widget);
        },
        subtitle(){
            return this.$store.getters['stage/w_use_range'] && [1, 2].includes(this.widget.data_type) ? this.$root.$t('dashboard.created_in_range') : '';
        },
        options(){
            return {
                chart: {
                    plotBackgroundColor: null,
                    plotBorderWidth: null,
                    plotShadow: false,
                    type: 'pie',
                },
                title: {
                    text: this.title,
                },
                subtitle: {
                    text: this.subtitle,
                },
                tooltip: {
                    pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> ({point.y})',
                },
                plotOptions: {
                    pie: {
                        allowPointSelect: true,
                        cursor: 'pointer',
                        dataLabels: {
                            enabled: false,
                        },
                        showInLegend: true,
                    },
                },
                legend: {
                    labelFormat: '{name} ({y})',
                },
                series: this.widget.series,
                credits: {
                    enabled: false,
                },
            }
        },
    },
}

</script>