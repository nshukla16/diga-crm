<style>
    .funnel {
        list-style-type: none;
        margin: 0;
        padding: 0;
        text-align: center;
        color: #fff;
        background-color: #fff;
    }
  .funnel li {
    margin: 0;
    /* background-color: #409ca9; */
    margin-bottom: 5px;
    position: relative;
    overflow: hidden;
  }

 .funnel li:before, .funnel li:after {
    content: '';
    border-bottom: 53px solid #fff;
  }

 .funnel li:before {
    border-right: 15px solid transparent;
    border-left: 0;
  }

 .funnel li:after {
    border-left: 15px solid transparent;
    border-right: 0;
  }

  .funnel li:nth-child(1):before, .funnel li:nth-child(1):after { width:15px; }
 .funnel li:nth-child(2):before, .funnel li:nth-child(2):after { width: 30px; }
 .funnel li:nth-child(3):before, .funnel li:nth-child(3):after { width: 45px; }
 .funnel li:nth-child(4):before, .funnel li:nth-child(4):after { width: 60px; }
 .funnel li:nth-child(5):before, .funnel li:nth-child(5):after { width: 75px; }
 .funnel li:nth-child(6):before, .funnel li:nth-child(6):after { width: 90px; }
 .funnel li:nth-child(7):before, .funnel li:nth-child(7):after { width: 105px; }
 .funnel li:nth-child(8):before, .funnel li:nth-child(8):after { width: 120px; }
 .funnel li:nth-child(9):before, .funnel li:nth-child(9):after { width: 135px; }
 .funnel li:nth-child(10):before, .funnel li:nth-child(10):after { width: 150px; }
 .funnel li:nth-child(11):before, .funnel li:nth-child(11):after { width: 165px; }

.table-inside-border {
    border-collapse: collapse;
    border-style: hidden;
    width: 100%;
    table-layout: fixed;
}

.table-inside-border td, .table-inside-border th {
    border: 1px solid whitesmoke;
}
</style>

<template lang="pug">
    section.h-100.px-2
        div.chart-title {{ $t("dashboard.funnel") }}
        table.text-center.h-100.w-100(v-if="widget.loading")
            tr
                td
                    div.loader.sm-loader
        template(v-if="!widget.loading")
            div.h-100(v-if="not_enough_data")
                table.text-center.h-100.w-100.no-enough-data-table
                    tr
                        td
                            div(style="font-size: 50px;")
                                i.fa.fa-database
                            div {{ $t('dashboard.Not_enough_data') }}
            div(:class="{ row: widget.size !== 1 }", style="padding-top:20px;")
                div(:class="{ 'col-md-9': widget.size !== 1 }")
                    ul.funnel(v-if="widget.funnel_items")
                        li(v-for="(val, i) in widget.funnel_items", :style="{'background-color': getRandomColor(widget.funnel_items.length, i)}").d-flex
                            div(style="flex: 1;")
                                table.table-inside-border(style="font-size:13px;")
                                    tr
                                        td(colspan="3") {{i == 0 ? $t('dashboard.Initial_layer') : val.service_state_name}}
                                    tr
                                        td {{val.count}}
                                        td {{$root.formatFinanceValue(i>0 ? (val.count / widget.funnel_items[0].count  * 100) : 100)}} %
                                        td {{$root.formatFinanceValue(val.sum)}} {{$root.current_currency.symbol}}
                div(:class="{ 'col-md-3': widget.size !== 1, 'text-center': widget.size === 1 }")
                    p.align-middle {{widget.rejected_count}} - {{$t('dashboard.Number_of_rejected_services')}}
                    p.align-middle {{Math.round(widget.time_to_reject)}} - {{$t('dashboard.Average_time_to_reject')}}
                    p.align-middle {{widget.funnel_items[widget.funnel_items.length - 1].count}} - {{$t('dashboard.Number_of_services') + widget.funnel_items[widget.funnel_items.length - 1].service_state_name}}
                    p.align-middle {{Math.round(widget.time_to_sold)}} - {{$t('dashboard.Average_time_to') + widget.funnel_items[widget.funnel_items.length - 1].service_state_name}}
</template>

<script>

export default {
    props: ['widget'],
    components: {
    },
    data() {
        return {
        }
    },
    mounted(){
    },
    methods: {
        getRandomColor(numOfSteps, step) {
            switch (step) {
            case 0:
                return '#47A2ED';
            case 1:
                return '#4BC871';
            case 2:
                return '#F6C445';
            case 3:
                return '#F39B37';
            case 4:
                return '#F1774D';
            default:
                return '#EF0B01';
            }
        },
    },
    computed: {
        not_enough_data(){
            return this.widget.funnel_items.length == 0;
        },
        use_range(){
            return this.$store.getters['stage/w_use_range'] ? this.$root.$t('dashboard.for_services_created_in_range') : '';
        },
    },
}

</script>