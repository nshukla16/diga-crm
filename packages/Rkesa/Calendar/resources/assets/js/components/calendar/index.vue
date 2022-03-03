<style>
    #calendar table .fc-head{
        color: #ffffff;
        text-transform: uppercase;
    }
    #calendar th{
        border: none;
    }
    #calendar .fc-head-container{
        border:none;
    }
    #calendar div.fc-widget-header{
        padding: 5px 0;
        border-radius: 5px;
        margin-right: 0;
    }
    #calendar .fc-event-container{
        padding: 0 5px 5px;
    }
    #calendar .fc-day-grid-event .fc-content {
        white-space: normal;
        padding: 5px;
        color: #ffffff;
    }
    #calendar .fc-event{
        border: none;
    }
    #calendar .fc-body > tr > .fc-widget-content{
        border:none;
    }
    #calendar .fc-day-number{
        float: left;
    }
    #calendar .fc-day-grid-container{
        margin-top: 10px;
    }
    #calendar .fc-scroller{
        height: auto !important;
        overflow-y: auto !important;
    }
    #calendar .fc-day-top.fc-other-month {
        opacity: 0.5;
    }
    .calendar-title{
        text-transform: uppercase;
        font-size: 20px;
        line-height: 38px;
        white-space: nowrap;
    }
    #calendarModal table td{
        vertical-align: top;
    }
    #calendar tfoot td {
        padding-left: 5px !important;
    }
    .go-to-this-day{
        float: right;
        margin-right: 5px;
        cursor: pointer;
    }
    .muted-color{
        opacity: 0.5 !important;
    }
    /* .fc-content-skeleton{
        max-height: 250px;
        overflow: auto;
    } */
</style>

<template lang="pug">
    div
        event_popup(:current_event="current_event", :current_contact="current_contact" @update="update_calendar")
        event_create_popup(:date_start="date_start" :date_finish="date_finish" :refresh_form="refresh_form" @update="update_calendar")
        component(v-if="$root.modules.calendar_extended == 1" v-bind:is="'calendar-extended'")
        div.row
            div.col-md-12
                div.diga-container
                    div.d-flex.flex-wrap(style="margin-left: -0.5rem; margin-right: -0.5rem;")
                        div.calendar-title.mx-2.mb-2 {{ title }}
                        select.form-control.mx-2.mb-2(v-model="selected_user", style="flex: 2;min-width: 150px;")
                            option(value="0") {{ $t("calendar.All") }}
                            option(v-for="user in users.filter(u => u.active === true)" v-bind:value="user.id" v-text="user.name")
                        select.form-control.mx-2.mb-2(v-model="selected_done", style="flex: 2;min-width: 150px;")
                            option(value="0" selected) {{ $t("calendar.Tasks_active") }}
                            option(value="1") {{ $t("calendar.Tasks_inactive") }}
                            option(value="2") {{ $t("calendar.Tasks_all") }}
                        select.form-control.mx-2.mb-2(v-model="selected_event_type_id" style="flex: 2;min-width: 150px;")
                            option(value="0") {{ $t("calendar.All_types") }}
                            option(v-for="event_type in event_types" v-bind:value="event_type.id" v-text="event_type.title")
                        //- component(v-if="$root.modules.calendar_extended == 1" v-bind:is="'calendar-extended-search'", v-model="selected_groups")
                        button.btn-diga.mx-2.mb-2(v-on:click="prev()") <
                        button.btn-diga.mx-2.mb-2(v-on:click="next()") >
                        button.btn-diga.mx-2.mb-2(v-on:click="today()") {{ $t("calendar.Today") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="this_month()") {{ $t("template.this_month") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="month()") {{ $t("calendar.Month") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="week()") {{ $t("calendar.Week") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="day()") {{ $t("calendar.Day") }}
                        bootstrap-toggle(data-size="mini" v-model="is_big", :options="{ on: $t('template.maximize'), off: $t('template.minimize')}", data-width="120", data-height="38", data-onstyle="default")
                    div.calendar-body
                        div.row
                            div.col-sm-12
                                div#calendar.has-toolbar
        div.clearfix
</template>

<script>
import 'fullcalendar-year-view';
import calendar_extended from '../../../../../../CalendarExtended/resources/assets/js/components/calendar_extended/index.vue';
import calendar_extended_search from '../../../../../../CalendarExtended/resources/assets/js/components/calendar_extended/search.vue';
require('fullcalendar-year-view/dist/fullcalendar.css');
import event_popup from './event_popup.vue';
import event_create_popup from './_form.vue';
import {mapGetters} from "vuex";
import moment from 'moment';

export default {
    components: {
        'calendar-extended': calendar_extended,
        'calendar-extended-search': calendar_extended_search,
        'event_popup': event_popup,
        'event_create_popup': event_create_popup,
    },
    data() {
        return {
            selected_user: this.$cookie.get('calendar-user') || this.$root.user.id,
            selected_event_type_id: this.$cookie.get('calendar-event-type') || 0,
            selected_done: this.$cookie.get('calendar-done') || 0,
            current_event: null,
            current_contact: null,
            title: "",
            //
            selected_groups: JSON.parse(this.$cookie.get('calendar-groups') || '[]'),
            date_start: null,
            date_finish: null,
            refresh_form: false,
            is_big: true,
        }
    },
    methods: {
        update_calendar(){
            this.$cookie.set('calendar-done', this.selected_done, { expires: '1Y' });
            this.$cookie.set('calendar-user', this.selected_user, { expires: '1Y' });
            this.$cookie.set('calendar-event-type', this.selected_event_type_id, { expires: '1Y' });
            this.$cookie.set('calendar-groups', JSON.stringify(this.selected_groups), { expires: '1Y' });
            this.$cookie.set('calendar-is-big', this.is_big, { expires: '1Y' });
            $('#calendar').fullCalendar('refetchEvents');
        },
        open_event(event){
            this.current_event = event;
            this.current_contact = this.current_event.client_contact;
            jQuery('#calendarModal').modal();
        },
        open_event_create(start, end){
            this.date_start = moment(start).format('YYYY-MM-DD HH:mm');
            this.date_finish = moment(end).format('YYYY-MM-DD HH:mm');
            this.refresh_form = !this.refresh_form;
            jQuery('#eventCreateModal').modal();
        },
        prev(){
            $('#calendar').fullCalendar('prev');
        },
        next(){
            $('#calendar').fullCalendar('next');
        },
        today(){
            $('#calendar').fullCalendar('changeView', 'basicDay', moment());
        },
        this_month(){
            $('#calendar').fullCalendar('changeView', 'month');
            $('#calendar').fullCalendar('today');
        },
        month(){
            $('#calendar').fullCalendar('changeView', 'month');
        },
        week(){
            $('#calendar').fullCalendar('changeView', 'basicWeek');
        },
        day(){
            $('#calendar').fullCalendar('changeView', 'basicDay');
        },
        gotoday: function(day){
            $('#calendar').fullCalendar('changeView', 'basicDay', day);
        },
        isMobile() {
            if(/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
                return true
            } else {
                return false
            }
        }
    },
    computed: {
        ...mapGetters({
            users: 'getUsers',
            event_types: 'getEventTypes',
            event_types_by_id: 'getEventTypesById',
        }),
    },
    mounted(){
        if (this.$cookie.get('calendar-is-big') === 'false'){
            this.is_big = false;
        }
        let $this = this;
        $('#calendar').fullCalendar({
            disableDragging: true,
            header: false,
            editable: true,
            lang: 'pt-br',
            timeFormat: 'H:mm',
            locale: this.$root.locale,
            defaultView: $this.isMobile() ? 'basicDay' : 'month',
            // eventLimitText: $this.$root.$t("template.more"),
            // eventLimit: true,
            // views: {
            //     dayGrid: {
            //         eventLimit: 5
            //     },
            //     timeGrid: {
            //         eventLimit: 5
            //     },
            // },
            events: function(start, end, timezone, callback){
                $this.$root.global_loading = true;
                $this.$http.get('/api/events?done=' + $this.selected_done + '&user_id=' + $this.selected_user + '&event_type_id=' + $this.selected_event_type_id + '&groups=' + $this.selected_groups.map(function(e){ return e.value; }) + '&start=' + start.format('YYYY-MM-DD') + '&end=' + end.format('YYYY-MM-DD')).then(res => {
                    let events = res.data;
                    events.map((e) => {
                        let event_type = $this.event_types_by_id[e.event_type_id];
                        e.backgroundColor = event_type.color;
                        if (e.done === 1){
                            e.className = ['muted-color'];
                        }
                        e.title = event_type.title;
                        if (event_type.show_description){
                            e.title += ' ' + e.description;
                        }
                        return e;
                    });
                    callback(events);
                }, res => {
                    $this.$toastr.e($this.$root.$t("template.Server_error"), $this.$root.$t("template.Error"));
                    $this.$root.global_loading = false;
                });
            },
            eventClick: function(event, jsEvent, view) {
                $this.open_event(event);
            },
            eventDrop: function(event) {
                if (confirm($this.$root.$t("calendar.AreYouSure"))) {
                    $this.$root.global_loading = true;
                    let payload = Object.assign({}, event);
                    if (payload.start){
                        payload.start = moment(payload.start).format('YYYY-MM-DD HH:mm:ss');
                    }
                    if (payload.end){
                        payload.end = moment(payload.end).format('YYYY-MM-DD HH:mm:ss');
                    }
                    delete payload.client_contact;
                    delete payload.source;

                    $this.$http.patch('/api/events/' + event.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            $this.$toastr.e(res.data.errmess, $this.$root.$t("template.Error"));
                        } else {
                            $this.$toastr.s($this.$root.$t("client.Task_saved"), $this.$root.$t("template.Success"));

                        }
                        $this.$root.global_loading = false;
                    }, res => {
                        $this.$toastr.e($this.$root.$t("template.Server_error"), $this.$root.$t("template.Error"));
                        $this.$root.global_loading = false;
                    });
                }
                else{
                    $this.update_calendar();
                }                
            },
            // Triggered while an event is being rendered. A hook for modifying its DOM.
            eventRender: function(event, element, view) {
                element.find('.fc-title').prepend(' <span class="fa ' + $this.event_types_by_id[event.event_type_id].icon + '"></span> ');
                if (event.end){
                    element.find('.fc-title').prepend(' <span><b> - ' + moment(event.end).format('H:mm') + '</b></span>');
                }
                if (event.client_contact){
                    element.find('.fc-title').append(' <span>' + $this.$root.fullName(event.client_contact) + '</span>');
                }

                if (event.service && $this.$root.global_settings.enable_service_name_in_event_in_calendar === true){
                    element.find('.fc-title').append(' <span>' + event.service.name + ' (' + $this.$root.formatFinanceValue(event.service.estimate_summ) + ' ' + $this.$root.current_currency.symbol + ') ' + '</span>');
                }

                $this.$bus.$emit('calendar_after_event_rendered', [event, element, view]);
            },
            // Triggered after all events have finished rendering.
            eventAfterAllRender: function(){
                // need to optimize speed
                $('.fc-row .fc-content-skeleton table').each(function(key, value){
                    let table = $(value);
                    if (table.has('tfoot').length == 0) {
                        if ($this.$root.global_settings.enable_total_by_day_in_calendar === true){
                            table.append('<tfoot><tr></tr><tr></tr></tfoot>')
                        }
                        else{
                            table.append('<tfoot><tr></tr></tfoot>')
                        }                        
                    } else {
                        table.find('tfoot tr').html('');
                    }
                    $(value).find('.fc-day-top').each(function(key2, value2) {
                        let current_date = $(value2).data('date');
                        let count = $('#calendar').fullCalendar('clientEvents', function (eventObj) {
                            return eventObj.start.format('YYYY-MM-DD') == current_date;
                        }).length;
                        table.find('tfoot tr').first().append('<td>' + $this.$root.$t('calendar.Total_events') + ': ' + count + '</td>');
                        if ($this.$root.global_settings.enable_total_by_day_in_calendar === true){
                            let sum = 0;
                            let array = $('#calendar').fullCalendar('clientEvents', function (eventObj) {
                            return eventObj.start.format('YYYY-MM-DD') == current_date;
                            });
                            if (array.length > 0){
                                sum = array.map(item => item.service ? item.service.estimate_summ : 0).reduce((prev, next) => prev + next);
                            }
                            table.find('tfoot tr').last().append('<td>' + $this.$root.$t('dashboard.Total_sum') + ': ' + $this.$root.formatFinanceValue(sum) + ' ' + $this.$root.current_currency.symbol + '</td>');
                        }
                        if ($(value2).find('.go-to-this-day').length == 0) {
                            $(value2).append('<span class="go-to-this-day"><i class="fa fa-arrow-right"></i></span>');
                        }
                        if ($(value2).find('.add-event').length == 0) {
                            $(value2).append('<span data-toggle="tooltip" data-placement="top" title="' + $this.$root.$t('client.Adicionar_tarefa') + '" class="add-event"><i style="margin-left:5px; margin-top:5px;" class="fa fa-plus-circle"></i></span>');
                        }
                    });
                });
                $('.go-to-this-day').click(function(){
                    $this.gotoday($(this).parent().data('date'));
                });
                $('.add-event').click(function(){
                    $this.open_event_create($(this).parent().data('date'), $(this).parent().data('date'));
                });
                $this.$bus.$emit('calendar_after_all_events_rendered', this);
                $this.$root.global_loading = false;
            },
            viewRender: function(){
                $this.title = $('#calendar').fullCalendar('getView').title;
            },
        });
        this.$bus.$on("calendar_refetch_events", this.update_calendar);
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('calendar.Calendar');
        $('#calendar').fullCalendar('option', 'locale', this.$root.user.site_language);
    },
    beforeDestroy: function() {
        this.update_calendar && this.$bus.$off("calendar_refetch_events", this.update_calendar);
    },
    watch: {
        selected_user(){ this.update_calendar(); },
        selected_done(){ this.update_calendar(); },
        selected_event_type_id(){ this.update_calendar(); },
        selected_groups(){ this.update_calendar(); },
        is_big(val){
            this.$cookie.set('calendar-is-big', val, { expires: '1Y' });
            if (val === false){
                $(".fc-content-skeleton").css({"max-height":"250px","overflow":"auto"});
            }else{
                $(".fc-content-skeleton").css({"max-height":"","overflow":""});
            }
        },
    },
}
</script>