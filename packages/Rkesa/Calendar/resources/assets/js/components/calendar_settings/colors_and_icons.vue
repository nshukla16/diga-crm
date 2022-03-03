<style>
    .icon{
        border: 1px solid #c7c7c7;
        border-right: none;
        line-height: 34px;
        text-align: center;
    }

    .colors-table td{
        padding: 5px;
    }
    .picker-container{
        position: relative;
    }
</style>

<template lang="pug">
    div(v-if="types")
        div.row
            div.mb-3(:class="{'col-12': !extended, 'col-12 col-md-6': extended}")
                div.diga-container.p-4
                    h2 {{ $t("template.Settings") }}
                    div.row
                        div.col-6.col-sm-6
                            label.control-label {{ $t('template.Move_events_on_the_next_day') }}
                        div.col-6.col-sm-6
                                div(style="width:120px;")
                                    bootstrap-toggle(data-size="mini" v-model="global_settings.move_events_to_next_day", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                    div.row(v-if="global_settings.move_events_to_next_day")
                        div.col-6.col-sm-6
                            label.control-label {{ $t('template.Time_of_moving_task') }}
                        div.col-6.col-sm-6
                            input.form-control(v-model="global_settings.move_events_to_next_day_time", type="time", style="width: 120px; margin-top: 5px;")
                    div.row
                        div.col-6.col-sm-6
                            label.control-label {{ $t('template.enable_totals_by_day_in_calendar') }}
                        div.col-6.col-sm-6
                                div(style="width:120px;")
                                    bootstrap-toggle(data-size="mini" v-model="global_settings.enable_total_by_day_in_calendar", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                    div.row
                        div.col-6.col-sm-6
                            label.control-label {{ $t('template.enable_service_name_in_calendar') }}
                        div.col-6.col-sm-6
                                div(style="width:120px;")
                                    bootstrap-toggle(data-size="mini" v-model="global_settings.enable_service_name_in_event_in_calendar", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                    h2 {{ $t("calendar.Event_types") }}
                    div.table-responsive
                        table.colors-table.w-100.mb-2
                            thead
                                tr
                                    td(style="width: 40px;") №
                                    td(style="width:18px;")
                                    td(style="min-width: 100px;") {{ $t("calendar.Title") }}
                                    td(style="width: 145px;min-width: 145px;") {{ $t("calendar.Color") }}
                                    td(style="width: 195px;min-width: 195px;") {{ $t("calendar.Icon") }}
                                    td(style="width: 200px;") {{ $t("calendar.Options") }}
                                    td(style="width: 85px;")
                            tbody
                                tr(v-for="(event_type,i) in types_ordered" style="margin-bottom: 5px;")
                                    td
                                        | {{ i+1 }}.
                                    td
                                        i.fa.fa-chevron-up(style="cursor: pointer;" v-on:click="event_type_up(event_type)")
                                        i.fa.fa-chevron-down(style="cursor: pointer;" v-on:click="event_type_down(event_type)")
                                    td
                                        input.form-control(v-model="event_type.title")
                                    td.picker-container
                                        sketch-picker(v-if="selected[i]" v-model="event_type.color" v-on-clickaway="hide_picker")
                                        div.color-icon.color(v-bind:style="{'background-color': event_type.color.hex}" v-on:click="show_picker(i)")
                                        input.form-control.settings-inputs(style="width: 100px;" v-model="event_type.color.hex")
                                    td
                                        div.color-icon.icon
                                            i(v-bind:class="['fa', event_type.icon]")
                                        input.form-control.settings-inputs(style="width: 150px;" v-model="event_type.icon")
                                    td
                                        input(type="checkbox" v-model="event_type.show_description")
                                        | &nbsp;{{ $t("calendar.Show_description") }}
                                    td
                                        button.btn.red.ml-2(v-on:click="remove(event_type)") {{ $t('template.Remove') }}
                    button.btn(v-on:click="add()") {{ $t('template.Add') }}
            calendar-extended(v-if="extended", :event_groups="groups")
        div.row
            div.col-12
                button.btn.btn-diga(v-on:click="save_settings") {{ $t("template.Save") }}
</template>

<script>
import calendar_extended from './../../../../../../CalendarExtended/resources/assets/js/components/calendar_extended_settings/index.vue';
import { mapGetters } from 'vuex';

export default {
    data() {
        return {
            types: null,
            groups: null,
            selected: [],
            removed: [],
        }
    },
    components: {
        'calendar-extended': calendar_extended,
    },
    watch: {
        c_event_types(){
            this.reload_types();
        },
        c_event_groups(){
            this.groups = JSON.parse(JSON.stringify(this.c_event_groups));
        },
    },
    created(){
        this.reload_types();
        this.groups = JSON.parse(JSON.stringify(this.c_event_groups));
    },
    methods: {
        reload_types(){
            this.types = JSON.parse(JSON.stringify(this.c_event_types));
            for (let i = 0; i < this.types.length; i++) {
                this.selected.push(false);
                this.types[i].color = {
                    hex: this.types[i].color,
                }
            }
        },
        save_settings(){
            if (this.extended) {
                this.$bus.$emit('calendar_settings_get_data');
            } else {
                this.data_received([]);
            }
        },
        data_received(calendar_extended_data){
            let payload = JSON.parse(JSON.stringify(this.$data));
            for (let i = 0; i < payload.types.length; i++) {
                payload.types[i].color = payload.types[i].color.hex;
            }
            payload.selected = null;
            payload.groups_data = calendar_extended_data;
            payload.move_events_to_next_day = this.global_settings.move_events_to_next_day;
            payload.move_events_to_next_day_time = this.global_settings.move_events_to_next_day_time;
            payload.enable_total_by_day_in_calendar = this.global_settings.enable_total_by_day_in_calendar;
            payload.enable_service_name_in_event_in_calendar = this.global_settings.enable_service_name_in_event_in_calendar;
            this.$root.global_loading = true;
            this.$http.post('/api/settings/calendar', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("calendar.Settings_saved"), this.$root.$t("template.Success"));
                    this.removed = [];
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        show_picker(i){
            Vue.set(this.selected, i, true);
        },
        hide_picker(){
            for (let i = 0; i < this.types.length; i++) {
                Vue.set(this.selected, i, false);
            }
        },
        add(){
            let event_type = {
                id: 0,
                title: '',
                color: {
                    hex: '#000000',
                },
                icon: 'fa-pencil',
                show_description: false,
                order: this.types.length + 1,
            };
            this.types.push(event_type);
        },
        remove(event_type){
            if (confirm(this.$root.$t("calendar.Are_you_sure_want_to_delete_event_type"))){
                this.removed.push(event_type.id);
                let index = this.types.indexOf(event_type);
                this.types.splice(index, 1);
                for (let i = index; i < this.types.length; i++){
                    this.types[i]['order']--;
                }
            }
        },
        event_type_up(event_type){
            if (event_type.order > 1){
                event_type.order--;
                let index = this.types.indexOf(event_type);
                this.types[index - 1].order++;
            }
        },
        event_type_down(event_type){
            if (event_type.order < this.types.length){
                event_type.order++;
                let index = this.types.indexOf(event_type);
                this.types[index + 1].order--;
            }
        },
    },
    computed: {
        types_ordered(){
            return this.types.sort(function(a, b) {
                if (a.order > b.order){
                    return 1;
                } else if (b.order > a.order){
                    return -1;
                } else {
                    return 0;
                }
            });
        },
        ...mapGetters({
            global_settings: 'getGlobalSettings',
            c_event_types: 'getEventTypes',
            c_event_groups: 'getEventGroups',
        }),
        extended(){
            return this.global_settings.modules.calendar_extended == 1;
        },
    },
    mounted(){
        this.$bus.$on("calendar_settings_data", this.data_received);
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('calendar.Calendar_settings');
    },
    beforeDestroy: function() {
        this.data_received && this.$bus.$off("calendar_settings_data", this.data_received);
    },
}
</script>