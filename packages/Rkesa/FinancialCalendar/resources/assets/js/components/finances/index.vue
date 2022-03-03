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
    .go-to-this-date{
        float: right;
        margin-right: 5px;
        cursor: pointer;
    }
</style>

<template lang="pug">
    div
        #add-payment.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-lg.modal-dialog-centered(role="document")
                .modal-content
                    .modal-header
                        h2.modal-title#myModalLabel {{ $t('calendar.New_payment') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body()
                        div
                            fieldset.form-group(:class="{ 'has-error': errors.has('payment_name') }")
                                label {{ $t('project.Name') }}
                                input.form-control.mr-2(v-model="current_payment.payment_name" name="payment_name", v-validate="'required'", v-bind:data-vv-as="$t('project.Name').toLowerCase()")
                                h6.help-block(v-show="errors.has('payment_name')") {{ errors.first('payment_name') }}
                            fieldset.form-group(:class="{ 'has-error': errors.has('payment_name') }")
                                label {{ $t('calendar.Amount') }} {{$root.current_currency.symbol}}
                                input.form-control.mr-2(type="number" min="0" v-model="current_payment.payment_amount", name="payment_amount", v-validate="'required'", v-bind:data-vv-as="$t('calendar.Amount').toLowerCase()")
                                h6.help-block(v-show="errors.has('payment_amount')") {{ errors.first('payment_amount') }}
                            fieldset.form-group(:class="{ 'has-error': errors.has('payment_date') }")
                                label {{ $t('gantt.Start_date') }}
                                date-picker(format="YYYY-MM-DD" v-model="current_payment.payment_date", :lang="$root.locale", :width="'100%'", name="payment_date", v-validate="'required'", v-bind:data-vv-as="$t('gantt.Start_date').toLowerCase()")
                                h6.help-block(v-show="errors.has('payment_date')") {{ errors.first('payment_date') }}
                            fieldset.form-group
                                label {{ $t('calendar.Description') }}
                                input.form-control.mr-2(v-model="current_payment.payment_description")
                            fieldset.form-group
                                label {{ $t('gantt.Estimate') }}
                                v-select.mb-3(
                                    :debounce='250',
                                    :on-search='get_estimates_options',
                                    :on-change='estimate_select',
                                    v-model="current_payment.selected_estimate",
                                    :options='tmp_estimates',
                                    :placeholder="$t('gantt.Choose_estimate')")
                                    template(slot="no-options") {{ $t('template.No_matching_options') }}
                            fieldset.form-group
                                label {{ $t('client.Client_name') }}
                                v-select(:debounce='250', :on-search='get_companies_options', :on-change='company_select', v-model="current_payment.payment_company", :options='companies', :placeholder="$t('client.Choose_company')")
                            fieldset.form-group(:class="{ 'has-error': errors.has('contact') }")
                                label.control-label {{ $t("client.Contact") }}
                                v-select(:debounce='250', :on-search='get_contacts_options', :on-change='contact_select', v-model="current_payment.payment_contact", :options='contacts', :placeholder="$t('client.Choose_contact')")
                            fieldset.form-group(:class="{ 'has-error': errors.has('service') }")
                                label.control-label {{ $t("client.Service") }}
                                v-select(:debounce='250', :on-search='get_services_options', :on-change='service_select', v-model="current_payment.selected_service", :options='services', :placeholder="$t('client.Service')")
                        button.btn.btn-secondary(@click='save_payment_event') {{ $t('calendar.Save_payment') }}
        event_popup(:current_event="current_event", :current_contact="current_contact")
        component(v-if="$root.modules.calendar_extended == 1" v-bind:is="'calendar-extended'")
        div.row
            div.col-md-12
                div.diga-container
                    div.d-flex.flex-wrap(style="margin-left: -0.5rem; margin-right: -0.5rem;")
                        //- component(v-if="$root.modules.calendar_extended == 1" v-bind:is="'calendar-extended-search'", v-model="selected_groups")
                        //- div(style="width: 100%")
                        div.calendar-title.mx-2.mb-2.float-left {{ title }}
                        select.form-control.mx-2.mb-2(v-model="selected_payment_status_id", style="flex: 2;min-width: 150px;")
                            option(value="0") {{ $t("template.payment_event_select_status") }}
                            option(value="null") {{ $t("template.created") }}
                            option(value="1" v-text="$t('template.payment_event_paid')")
                            option(value="2" v-text="$t('template.payment_event_waiting_payment')")
                            option(value="3" v-text="$t('template.payment_event_needs_attention')")
                            option(value="4" v-text="$t('template.payment_event_problem')")
                            option(value="5" v-text="$t('template.payment_event_canceled')")
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="day()") {{ $t("calendar.Day") }}
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="week()") {{ $t("calendar.Week") }}
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="month()") {{ $t("calendar.Month") }}
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="year()") {{ $t("gantt.Year") }}
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="today()") {{ $t("calendar.Today") }}
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="this_month()") {{ $t("template.this_month") }}
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="next()") >
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="prev()") <
                        button.btn-diga.mx-2.mb-2.float-right(v-on:click="add_payment()") {{ $t("calendar.Add_payment") }}

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
    import moment from 'moment';
    import {mapGetters} from "vuex";

    export default {
        components: {
            'calendar-extended': calendar_extended,
            'calendar-extended-search': calendar_extended_search,
            'event_popup': event_popup,
        },
        data() {
            return {
                current_payment: [],
                tmp_estimates: [],
                companies: [],
                contacts: [],
                services: [],
                selected_user: this.$cookie.get('calendar-user') || this.$root.user.id,
                selected_event_type_id: this.$cookie.get('calendar-event-type') || 0,
                current_event: null,
                current_contact: null,
                title: "",
                //
                selected_groups: JSON.parse(this.$cookie.get('calendar-groups') || '[]'),
                selected_payment_status_id: null,
            }
        },
        methods: {
            update_calendar(){
                // this.$cookie.set('calendar-done', this.selected_done, { expires: '1Y' });
                // this.$cookie.set('calendar-user', this.selected_user, { expires: '1Y' });
                // this.$cookie.set('calendar-event-type', this.selected_event_type_id, { expires: '1Y' });
                // this.$cookie.set('calendar-groups', JSON.stringify(this.selected_groups), { expires: '1Y' });
                $('#calendar').fullCalendar('refetchEvents');
            },
            open_event(event){
                this.current_event = event;
                this.current_contact = this.current_event.client_contact;
                jQuery('#calendarModal').modal();
            },
            close_modal(){
               $('#add-payment').modal('hide');
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
            year(){
                $('#calendar').fullCalendar('changeView', 'year');
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
            reload(){
                $('#calendar').fullCalendar('refetchEvents');
            },
            add_payment(){
                this.current_payment = {
                    selected_estimate: null,
                    payment_name: null,
                    payment_date: null,
                    payment_amount: null,
                    payment_description: null,
                    estimate_number: null,
                    payment_company: null,
                    payment_contact: null,
                    selected_service: null,
                };
                $('#add-payment').modal('show');
            },
            get_estimates_options(search, loading) {
                loading(true);
                this.$http.get('/api/estimates?limit=20&query=' + search).then(res => {
                    var processedData = [];
                    let $this = this;
                    res.data.rows.forEach(function(i){
                        processedData.push({'label': $this.$root.estimate_number(i), 'value': i.id});
                    });
                    this.tmp_estimates = processedData;
                    loading(false);
                }, res => {
                    this.$toastr.e(this.$t("template.Something_bad_happened"), this.$t("template.Error"));
                })
            },
            estimate_select(res){
                this.selected_estimate = res;
            },
            getResults() {
                this.$http.get('/api/estimate_plannings?' + this.$root.params(this.table.query)).then(res => {
                    return res.json();
                }).then(data => {
                    if (data.errcode == 1) {
                        this.$toastr.e(data.errmess, this.$t("template.Error"));
                    } else {
                        this.table.data = data.rows;
                        this.table.total = data.total;
                    }
                }, res => {
                    this.$toastr.e(this.$t("template.Server_error"), this.$t("template.Error"));
                });
            },
            save_payment_event() {
                this.$validator.validateAll().then(result => {
                    if (!result) {
                        this.$toastr.w(this.$t("template.Need_to_fill"), this.$t("template.Warning"));
                        return;
                    }
                    let start = moment(this.current_payment.payment_date).format('YYYY-MM-DD');
                    this.$http.post('/api/payment_events', {
                        title: this.current_payment.payment_name,
                        start: start,
                        amount: this.current_payment.payment_amount,
                        description: this.current_payment.payment_description,
                        estimate_id: this.current_payment.selected_estimate ? this.current_payment.selected_estimate.value : null,
                        client_id: this.current_payment.payment_company ? this.current_payment.payment_company.value : null,
                        contact_id: this.current_payment.payment_contact ? this.current_payment.payment_contact.value: null,
                        service_id: this.current_payment.selected_service ? this.current_payment.selected_service.value: null,
                    })
                        .then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$t("template.Error"));
                            } else {
                                //SHOULD RELOAD THE CALENDAR HERE
                                // this.load();
                                this.reload();
                                $('.fc-day-header').each(function (key11, value11) {
                                    // $(value11).prepend('<span class="go-to-this-date"><i class="fa fa-arrow-right"></i></span>');
                                    $('.go-to-this-date:nth-child(1)').detach();
                                });
                                $('#add-payment').modal('hide');
                            }
                            this.$root.global_loading = false;
                        }, res => {
                            this.$toastr.e(this.$t("template.Server_error"), this.$t("template.Error"));
                            this.$root.global_loading = false;
                        });
                });
            },
            get_companies_options(search, loading) {
                loading(true);
                this.$http.get('/api/companies?format=json&limit=20&query=' + search).then(res => {
                    var processedData = [];
                    let $this = this;
                    res.data.rows.forEach(function(i){
                        processedData.push({'label': i.name, 'value': i.id});
                    });
                    this.companies = processedData;
                    loading(false);
                }, res => {
                    this.$toastr.e(this.$t("template.Something_bad_happened"), this.$t("template.Error"));
                })
            },
            company_select(res){
                this.currentContact.client_id = res == null ? null : res.value;
                this.selected = res;
            },
            get_services_options(search, loading) {
                loading(true);
                var url = '/api/services?query=' + search;

                if (this.current_payment.payment_contact){
                    url += '&client_contact_id=' + this.current_payment.payment_contact.value;
                }
                this.$http.get(url).then(res => {
                    var processedData = [];
                    let $this = this;
                    res.data.rows.forEach(function(i){
                        processedData.push({'label': (i.name === null ? '' : i.name.substr(0, 60) + '... ') + $this.$root.service_number(i), 'value': i.id});
                    });
                    this.services = processedData;
                    loading(false);
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                })
            },
            service_select(res){
                if(res === null){
                    this.current_payment.selected_service = null;
                }
                if (typeof res === 'object' && res !== null) {
                    this.current_payment.selected_service = res;
                }
            },
            get_contacts_options(search, loading) {
                loading(true);
                this.$http.get('/api' + this.$root.contact_or_client_store() + '?format=json&limit=20&query=' + search).then(res => {
                    var processedData = [];
                    let $this = this;
                    res.data.rows.forEach(function(i){
                        processedData.push({'label': $this.$root.fullName(i), 'value': i.id});
                    });
                    this.contacts = processedData;
                    loading(false);
                }, res => {
                    this.$toastr.e(this.$t("template.Something_bad_happened"), this.$t("template.Error"));
                })
            },
            contact_select(res){
                if (res == null){
                    this.currentService.client_contact_id = null;
                    this.selected = null;
                } else {
                    this.currentService.client_contact_id = res.value;
                    this.selected = res;
                }
            },
            load(){
                let $this = this;
                $('#calendar').fullCalendar({
                    disableDragging: true,
                    header: false,
                    editable: true,
                    displayEventTime: false,
                    lang: 'pt-br',
                    timeFormat: 'H:mm',
                    locale: this.$root.locale,
                    events: function(start, end, timezone, callback){
                        $this.$root.global_loading = true;
                        $this.$http.get('/api/payment_events?status_id=' + $this.selected_payment_status_id + '&start=' + start.format('YYYY-MM-DD') + '&end=' + end.format('YYYY-MM-DD')).then(res => {
                            let events = res.data;
                            events.map((e) => {
                                e.name = e.title;
                                e.title = e.title + ': ' + e.amount + ' ' + $this.$root.current_currency.symbol;
                                if (e.status_id > 0){
                                    switch(e.status_id){
                                        case 1:
                                            e.backgroundColor = "#1e7e34";
                                            break;
                                        case 2:
                                            e.backgroundColor = "#545b62";
                                            break;     
                                        case 3:
                                            e.backgroundColor = "#1d2124";
                                            break;
                                        case 4:
                                            e.backgroundColor = "#d39e00";
                                            break;
                                        case 5:
                                            e.backgroundColor = "lightgrey";
                                            break;                                                                                                                                                                           
                                    }                                    
                                }
                                return e;
                            });
                            callback(events);
                        }, res => {
                            $this.$toastr.e($this.$t("template.Server_error"), $this.$t("template.Error"));
                            $this.$root.global_loading = false;
                        });
                    },
                    eventClick: function(event, jsEvent, view) {
                        $this.open_event(event);
                    },
                    // Triggered while an event is being rendered. A hook for modifying its DOM.
                    eventRender: function(event, element, view) {
                        if (event.estimate_pay_stage !== null){
                            if (event.estimate_pay_stage.email_template_id !== null){
                                element.find('.fc-title').prepend('<span title="' + $this.$t("estimate.Email_auto_send") + '" class="fa fa-envelope"></span> ');
                            }
                        }
                        // element.find('.fc-title').append(' <span>' + $this.$root.fullName(1) + '</span>');
                        $this.$bus.$emit('calendar_after_event_rendered', [event, element, view]);
                    },
                    // Triggered after all events have finished rendering.
                    eventAfterAllRender: function(view){
                        // need to optimize speed

                        $('.fc-row .fc-content-skeleton table').each(function(key, value){
                            let table = $(value);
                            if (table.has('tfoot').length == 0) {
                                table.append('<tfoot><tr></tr></tfoot>')
                            } else {
                                table.find('tfoot tr').html('');
                            }

                            if (view.name === 'month'){

                                $(value).find('.fc-day-top').each(function(key2, value2) {
                                    let current_date = $(value2).data('date');
                                    let payments_in_current_date = $('#calendar').fullCalendar('clientEvents', function (eventObj) {
                                        return eventObj.start.format('YYYY-MM-DD') == current_date;
                                    });
                                    let total_amount = payments_in_current_date.reduce((a, b) => +a + +b.amount, 0);
                                    total_amount = Math.round(total_amount * 100) / 100;
                                    table.find('tfoot tr').append('<td>' + $this.$t('project.Total') + ': ' + total_amount + ' ' + $this.$root.current_currency.symbol + '</td>');
                                    if ($(value2).find('.go-to-this-day').length == 0) {
                                        $(value2).append('<span class="go-to-this-day"><i class="fa fa-arrow-right"></i></span>');
                                    }
                                });
                            } else if (view.name === 'year'){
                                $(value).find('tbody tr:first-child td').each(function(key2, value2) {
                                    //let current_date = $(value2).data('date');
                                    let payments_in_current_date = $('#calendar').fullCalendar('clientEvents', function (eventObj) {
                                        return eventObj.start.format('M') == key2 + 1;
                                    });
                                    let total_amount = payments_in_current_date.reduce((a, b) => +a + +b.amount, 0);
                                    total_amount = Math.round(total_amount * 100) / 100;
                                    table.find('tfoot tr').append('<td>' + $this.$t('project.Total') + ': ' + total_amount + ' ' + $this.$root.current_currency.symbol + '</td>');
                                });

                                $('.fc-day-header').each(function (key11, value11) {
                                    $(value11).attr('data-date', moment($(value11).attr('data-date')).format('YYYY-MM'));
                                    $(value11).prepend('<span class="go-to-this-date"><i class="fa fa-arrow-right"></i></span>');
                                });
                            }
                        });
                        $('.go-to-this-day').click(function(){
                            $this.gotoday($(this).parent().data('date'));
                        });
                        $('.go-to-this-date').click(function(){
                            $('#calendar').fullCalendar('changeView', 'month');
                            $('#calendar').fullCalendar('gotoDate', $(this).parent().data('date'));
                        });
                        $this.$bus.$emit('calendar_after_all_events_rendered', this);
                        $this.$root.global_loading = false;
                    },
                    viewRender: function(){
                        $this.title = $('#calendar').fullCalendar('getView').title;
                    },
                });
                this.$bus.$on("calendar_refetch_events", this.update_calendar);
                document.title = this.$root.global_settings.site_name + ' | ' + this.$t('calendar.Calendar');
                $('#calendar').fullCalendar('option', 'locale', this.$root.user.site_language);
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
            this.load();
        },
        beforeDestroy: function() {
            this.update_calendar && this.$bus.$off("calendar_refetch_events", this.update_calendar);
        },
        watch: {
            selected_user(){ this.update_calendar(); },
            selected_event_type_id(){ this.update_calendar(); },
            selected_groups(){ this.update_calendar(); },
            selected_payment_status_id(){ this.update_calendar(); },
        },
    }
</script>