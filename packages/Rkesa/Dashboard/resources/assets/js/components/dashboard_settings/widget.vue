<template lang="pug">
    div.mb-3(:class="[size == 1 || size == null ? 'col-12 col-md-3' : 'col-12 col-md-6']")
        div.diga-container.p-4
            div.float-right.clickable(v-on:click="remove_widget")
                i.fa.fa-times
            div.mb-3
                .form-group
                    label(v-text="$t('dashboard.graph_data_type')")
                    select.form-control(v-model="widget_data_type")
                        optgroup(:label="$t('dashboard.actual_data')")
                            option(v-text="$t('dashboard.statuses')", :value="1")
                            option(v-text="$t('dashboard.referrers')", :value="2")
                            option(v-text="$t('dashboard.companies_referrers')", :value="8")
                            option(v-text="$t('dashboard.Status_duration')", :value="7")
                            option(v-text="$t('dashboard.avg_status_time')", :value="3")
                            option(v-text="$t('dashboard.avg_status_price')", :value="4")
                        optgroup(:label="$t('dashboard.retrospective_data')")
                            option(v-text="$t('dashboard.services_with_state_count')", :value="5")
                            option(v-text="$t('dashboard.services_with_state_sum')", :value="6")
                            option(v-text="$t('dashboard.funnel')", :value="9")
                            option(v-text="$t('dashboard.Regional_sales')", :value="10")
            div.mb-3
                .form-group
                    label(for="d-bar-graph-size" v-text="$t('dashboard.graph_size')")
                    select#d-bar-graph-size.form-control(v-model="size", style="margin-bottom:10px")
                        option(v-text="$t('dashboard.normal')", :value="1")
                        option(v-text="$t('dashboard.big')", :value="2")
            div.mb-3
                .form-group
                    label(v-text="$t('dashboard.graph_type')")
                    select.form-control(v-model="widget_type")
                        // See "Widget data & charts" in readme.md
                        option(v-text="$t('dashboard.Bar_chart')", :value="1" v-if="widget_data_type != 7 && widget_data_type != 9 && widget_data_type != 10")
                        option(v-text="$t('dashboard.Line_chart')", :value="2" v-if="widget_data_type != 1 && widget_data_type != 2 && widget_data_type != 8 && widget_data_type != 7  && widget_data_type != 9 && widget_data_type != 10")
                        option(v-text="$t('dashboard.Pie_chart')", :value="3" v-if="widget_data_type == 1 || widget_data_type == 2 || widget_data_type == 8")
                        option(v-text="$t('dashboard.Table')", :value="4" v-if="widget_data_type == 7")
                        option(v-text="$t('dashboard.custom')", :value="5" v-if="widget_data_type === 9 || widget_data_type === 10")
            div(v-if="[3,4,5,6,7].includes(widget_data_type)")
                label(v-text="$t('dashboard.Additional_settings')")
                div.mb-3
                    label(for="d-line-status", v-text="$t('dashboard.select_status')")
                    select#d-line-status.form-control(v-model="status")
                        option(v-for="status in not_removed_service_states", v-if="status.type == 0" v-text="status.name", :value="status.id")
                duration_chart(v-if="widget_data_type == 7", :id="id")
            div(v-if="widget_data_type == 9")
                label {{$t('dashboard.Additional_settings')}}
                div.mb-3
                    label(v-text="$t('dashboard.select_reject_state')")
                    select.form-control(v-model="reject_state_id")
                        option(v-for="status in not_removed_service_states", v-if="status.type == 0" v-text="status.name", :value="status.id")
                    div(style="margin-top: 20px;")
                        label.typo__label {{$t('dashboard.select_participating_states')}}
                        multiselect(
                            v-model="funnel_values",
                            :options="state_options",
                            :multiple="true",
                            :close-on-select="false",
                            :clear-on-select="false",
                            :preserve-search="true",
                            :placeholder="$t('dashboard.select_participating_states')"
                            label="name",
                            track-by="name",
                            :selectLabel="$t('dashboard.press_enter_to_select')",
                            :deselectLabel="$t('dashboard.press_enter_to_remove')",
                            :selectedLabel="$t('dashboard.option_selected')",
                        )
            div(v-if="widget_data_type == 10")
                label {{$t('dashboard.Additional_settings')}}
                div.mb-3
                    label(v-text="$t('dashboard.Start_status')")
                    select.form-control(v-model="initial_state_id")
                        option(v-for="status in not_removed_service_states", v-if="status.type == 0" v-text="status.name", :value="status.id")
                    label(v-text="$t('dashboard.Sale_status')")
                    select.form-control(v-model="sale_state_id")
                        option(v-for="status in not_removed_service_states", v-if="status.type == 0" v-text="status.name", :value="status.id")
                    div(style="margin-top: 20px;")
                        label.typo__label {{$t('dashboard.Select_fields')}}
                        multiselect(
                            v-model="selected_columns",
                            :options="columns",
                            :multiple="true",
                            :close-on-select="false",
                            :clear-on-select="false",
                            :preserve-search="true",
                            :placeholder="$t('dashboard.Select_fields')"
                            label="name",
                            track-by="name",
                            :selectLabel="$t('dashboard.press_enter_to_select')",
                            :deselectLabel="$t('dashboard.press_enter_to_remove')",
                            :selectedLabel="$t('dashboard.option_selected')",
                        )

</template>

<script>
import duration_chart from './charts/duration_chart.vue';
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    components: {
        duration_chart,
    },
    data() {
        return {
            reject_state_id: 1,
            sale_state_id: null,
            initial_state_id: null,
            funnel_values: [],
            state_options: [],
            columns: [],
            selected_columns: [],
        }
    },
    mounted() {
        let data = this.widget ? this.widget.data : null;
        if (data != "" && data != null){
            let obj = JSON.parse(this.widget.data);
            this.reject_state_id = obj.reject_state_id;
            this.funnel_values = obj.funnel_values;

            this.initial_state_id = obj.initial_state_id;
            this.sale_state_id = obj.sale_state_id;
            this.selected_columns = obj.selected_columns;
        }
        this.state_options =
            this.not_removed_service_states
                .filter(s => s.id !== this.reject_state_id)
                .map(function (s) {
                    return {id: s.id, name: s.name};
                });
        this.columns = [
            {id: 1, name: this.$root.$t("dashboard.Total_services")},
            {id: 2, name: this.$root.$t("dashboard.Total_services_in_sale_status")},
            {id: 3, name: this.$root.$t("dashboard.Total_services_in_sale_status_percent")},
            {id: 4, name: this.$root.$t("dashboard.Average_decision_time")},
            {id: 5, name: this.$root.$t("dashboard.Average_sum")},
            {id: 6, name: this.$root.$t("dashboard.Total_sum")},
            {id: 7, name: this.$root.$t("dashboard.Average_margin")},
            {id: 8, name: this.$root.$t("dashboard.Number_of_second_buys")},
        ];
    },
    methods: {
        remove_widget(){
            if (confirm(this.$root.$t("dashboard.Are_you_sure_want_to_delete_widget"))) {
                this.$store.dispatch('dashboard/removeWidget', this.id);
            }
        },
    },
    watch: {
        reject_state_id(val){
            var obj = {
                reject_state_id: val,
                funnel_values: this.funnel_values,
            };
            this.$store.dispatch('dashboard/updateWidgetData', { id: this.id, value: JSON.stringify(obj) });
        },
        funnel_values(vals){
            var obj = {
                reject_state_id: this.reject_state_id,
                funnel_values: vals,
            };
            this.$store.dispatch('dashboard/updateWidgetData', { id: this.id, value: JSON.stringify(obj) });
        },
        initial_state_id(val){
            var obj = {
                initial_state_id: val,
                sale_state_id: this.sale_state_id,
                selected_columns: this.selected_columns,
            };
            this.$store.dispatch('dashboard/updateWidgetData', { id: this.id, value: JSON.stringify(obj) });
        },
        sale_state_id(vals){
            var obj = {
                initial_state_id: this.initial_state_id,
                sale_state_id: vals,
                selected_columns: this.selected_columns,
            };
            this.$store.dispatch('dashboard/updateWidgetData', { id: this.id, value: JSON.stringify(obj) });
        },
        selected_columns(vals){
            var obj = {
                initial_state_id: this.initial_state_id,
                sale_state_id: this.sale_state_id,
                selected_columns: vals,
            };
            this.$store.dispatch('dashboard/updateWidgetData', { id: this.id, value: JSON.stringify(obj) });
        },
    },
    computed: {
        ...mapGetters({
            not_removed_service_states: 'getNotRemovedServiceStates',
        }),
        widget(){
            return this.$store.getters['dashboard/getWidgetsById'][this.id];
        },
        widget_type: {
            get() {
                return this.widget ? this.widget.widget_type : 1;
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetType', { id: this.id, value: value });
            },
        },
        widget_data_type: {
            get() {
                return this.widget ? this.widget.data_type : 1;
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetDataType', { id: this.id, value: value });
            },
        },
        size: {
            get() {
                return this.widget ? this.widget.size : 1;
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetSize', { id: this.id, value: value });
            },
        },
        status: {
            get() {
                return this.widget ? this.widget.service_state_id : 0;
            },
            set(value) {
                this.$store.dispatch('dashboard/updateWidgetStatus', { id: this.id, value: value });
            },
        },
    },
}
</script>