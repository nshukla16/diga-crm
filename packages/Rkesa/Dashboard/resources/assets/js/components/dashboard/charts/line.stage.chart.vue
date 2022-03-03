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

import moment from 'moment';
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
    methods: {
        get_service_in_state_according_to_date: function(year, month){
            this.$http.post('/api/me/dashboard/widget_more', {year: year, month: month, id: this.widget.id}).then(response => {
                this.$emit('popup_line', response.body.widget, this.widget.state.name + ' ' + this.$root.$t('template.months')[month - 1] + ' ' + year);
            }, error => {
                console.log(error);
            });
        },
    },
    computed: {
        not_enough_data(){
            return this.widget.series.length == 0 || (this.widget.series.length == 1 && this.widget.series[0].data.every(e => e === 0));
        },
        title(){
            // return this.$store.getters['stage/getChartTitle'](this.widget.id);
            return this.$root.getChartTitle(this.widget);
        },
        options() {
            let $this = this;
            return {
                title: {
                    text: this.title,
                },
                yAxis: {
                    title: {
                        text: this.widget.y_title,
                    },
                },
                xAxis: {
                    categories: this.$root.$t('template.months'),
                    title: {
                        text: this.widget.x_title,
                    },
                },
                plotOptions: {
                    line: {
                        cursor: 'pointer',
                    },
                    series: {
                        cursor: 'pointer',
                        events: {
                            click: function (event) {
                                if ($this.widget.data_type == 5) {
                                    $this.$root.global_loading = true;
                                    $this.get_service_in_state_according_to_date(event.point.series.name, event.point.x + 1)
                                }
                            },
                        },
                    },
                },
                legend: {
                    layout: 'horizontal',
                    align: 'center',
                    verticalAlign: 'bottom',
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