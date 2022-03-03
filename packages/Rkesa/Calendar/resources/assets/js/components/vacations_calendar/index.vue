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
</style>

<template lang="pug">
    div
        event_create_popup(:event="current_event", @update="update_calendar")
        div.row
            div.col-md-12
                div.diga-container
                    h1.text-center {{$t('template.vacations_calendar')}}
                    div.d-flex.flex-wrap(style="margin-left: -0.5rem; margin-right: -0.5rem;")
                        div.calendar-title.mx-2.mb-2 {{ title }}
                        select.form-control.mx-2.mb-2(v-model="selected_user", style="flex: 2;min-width: 150px;")
                            option(value="0") {{ $t("calendar.All") }}
                            option(v-for="user in users.filter(u => u.active === true)" v-bind:value="user.id" v-text="user.name")
                        select.form-control.mx-2.mb-2(v-model="selected_approved", style="flex: 2;min-width: 150px;")
                            option(value="0" selected) {{ $t("template.See_all") }}
                            option(value="1") {{ $t("template.non_approved") }}
                            option(value="2") {{ $t("template.approved") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="open_event_create()") {{$t("template.new_vacation")}}
                        button.btn-diga.mx-2.mb-2(v-on:click="prev()") <
                        button.btn-diga.mx-2.mb-2(v-on:click="next()") >
                        button.btn-diga.mx-2.mb-2(v-on:click="today()") {{ $t("calendar.Today") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="this_month()") {{ $t("template.this_month") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="month()") {{ $t("calendar.Month") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="week()") {{ $t("calendar.Week") }}
                        button.btn-diga.mx-2.mb-2(v-on:click="day()") {{ $t("calendar.Day") }}
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
// import event_popup from './event_popup.vue';
import event_create_popup from './_form.vue';
import {mapGetters} from "vuex";
import moment from 'moment';

export default {
    components: {
        'calendar-extended': calendar_extended,
        'calendar-extended-search': calendar_extended_search,
        // 'event_popup': event_popup,
        'event_create_popup': event_create_popup,
    },
    data() {
        return {
            selected_user: 0,//this.$root.user.id,
            selected_approved: 0,
            current_event: null,
            title: "",
            //
            date_start: null,
            date_finish: null,
        }
    },
    methods: {
        update_calendar(){
            $('#calendar').fullCalendar('refetchEvents');
        },
        open_event(event){
            this.current_event = event;
            jQuery('#eventCreateModal').modal();
        },
        open_event_create(start, end){
            this.current_event = {
                start: moment(),
                end: moment(),
                user_id: this.$root.user.id,
                is_approved: false,
            };
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
    },
    computed: {
        ...mapGetters({
            users: 'getUsers',
            users_by_id: 'getUsersById',
            event_types: 'getEventTypes',
            event_types_by_id: 'getEventTypesById',
        }),
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$t('template.vacations_calendar');
        let $this = this;
        $('#calendar').fullCalendar({
            disableDragging: false,
            header: false,
            editable: true,
            eventResizableFromStart: true,
            lang: 'pt-br',
            timeFormat: 'H:mm',
            locale: this.$root.locale,
            // selectable: true,
            // select: function(start, end) {
            //     $this.open_event_create(start, end);
            // },
            // dayClick: function(info){
            //     $this.open_event_create(info, info);
            // },
            eventDrop: function(event) {
                if (event.is_approved === true && $this.$root.user.can_approve_vacations !== true) {
                    $this.$toastr.w($this.$root.$t("template.cannot_change_vacation"), $this.$root.$t("template.Warning"));
                    $this.update_calendar();
                    return;
                }
                if (confirm($this.$root.$t("calendar.AreYouSure"))) {
                    $this.$root.global_loading = true;
                    var payload = {};
                    payload.start = event.start;
                    payload.end = event.end;
                    payload.user_id = event.user_id;
                    payload.is_approved = event.is_approved;

                    $this.$http.patch('/api/vacations/' + event.id, payload).then(res => {
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
            eventResize: function(event) {
                if (event.is_approved === true && $this.$root.user.can_approve_vacations !== true) {
                    $this.$toastr.w($this.$root.$t("template.cannot_change_vacation"), $this.$root.$t("template.Warning"));
                    $this.update_calendar();
                    return;
                }
                if (confirm($this.$root.$t("calendar.AreYouSure"))) {
                    $this.$root.global_loading = true;
                    var payload = {};
                    payload.start = event.start;
                    payload.end = event.end;
                    payload.user_id = event.user_id;
                    payload.is_approved = event.is_approved;

                    $this.$http.patch('/api/vacations/' + event.id, payload).then(res => {
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
            events: function(start, end, timezone, callback){
                $this.$root.global_loading = true;
                $this.$http.get('/api/vacations?approved=' + $this.selected_approved + '&user_id=' + $this.selected_user + '&start=' + start.format('YYYY-MM-DD') + '&end=' + end.format('YYYY-MM-DD')).then(res => {
                    let events = res.data.rows;
                    events.map((e) => {
                        e.backgroundColor = '#'+(Math.random() * 0xFFFFFF << 0).toString(16).padStart(6, '0');                   
                        e.title = $this.users_by_id[e.user_id].name;
                        return e;
                    });
                    callback(events);
                    $this.$root.global_loading = false;
                }, res => {
                    $this.$toastr.e($this.$root.$t("template.Server_error"), $this.$root.$t("template.Error"));
                    $this.$root.global_loading = false;
                });
            },
            eventClick: function(event, jsEvent, view) {
                $this.open_event(event);
            },
            // Triggered while an event is being rendered. A hook for modifying its DOM.
            // eventRender: function(event, element, view) {
                // element.find('.fc-title').prepend('<span class="fa ' + $this.event_types_by_id[event.event_type_id].icon + '"></span> ');
                // if (event.client_contact){
                //     element.find('.fc-title').append(' <span>' + $this.$root.fullName(event.client_contact) + '</span>');
                // }

                // $this.$bus.$emit('calendar_after_event_rendered', [event, element, view]);
            // },
            // Triggered after all events have finished rendering.
            eventAfterAllRender: function(){
                // need to optimize speed
                $('.fc-row .fc-content-skeleton table').each(function(key, value){
                    let table = $(value);
                    if (table.has('tfoot').length == 0) {
                        table.append('<tfoot><tr></tr></tfoot>')
                    } else {
                        table.find('tfoot tr').html('');
                    }
                    $(value).find('.fc-day-top').each(function(key2, value2) {
                        let current_date = $(value2).data('date');
                        let count = $('#calendar').fullCalendar('clientEvents', function (eventObj) {
                            return eventObj.start.format('YYYY-MM-DD') == current_date;
                        }).length;
                    });
                });

                $this.$bus.$emit('calendar_after_all_events_rendered', this);
                $this.$root.global_loading = false;
            },
            viewRender: function(){
                $this.title = $('#calendar').fullCalendar('getView').title;
            },
        });
        this.$bus.$on("calendar_refetch_events", this.update_calendar);
        $('#calendar').fullCalendar('option', 'locale', this.$root.user.site_language);
    },
    beforeDestroy: function() {
        this.update_calendar && this.$bus.$off("calendar_refetch_events", this.update_calendar);
    },
    watch: {
        selected_user(){ this.update_calendar(); },
        selected_approved(){ this.update_calendar(); },
    },
}
</script>