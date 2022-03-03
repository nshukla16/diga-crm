<template lang="pug">
    div.modal.fade(id="calendarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true")
        div.modal-dialog.modal-lg.modal-dialog-centered(role="document")
            div.modal-content(v-if="current_event != null")
                div.modal-header
                    h5.modal-title#exampleModalLabel {{ $t("calendar.Payment_info") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                        span(aria-hidden="true") &times;
                div.modal-body
                    div
                        span.font-weight-bold {{ $t("calendar.Name") }}:
                        span.ml-1 {{ event_title }}
                    div
                        span.font-weight-bold {{ $t("calendar.Date") }}:
                        span.ml-1 {{ get_event_datetime(current_event.start) }}
                    div
                        span.font-weight-bold {{ $t("calendar.Description") }}:
                        span.ml-1 {{ current_event.description }}
                    div(v-if="current_event.estimate")
                        span.font-weight-bold {{ $t("hr.Estimate") }}:
                        span.ml-1
                            //- a(:href="'/estimates/'+current_event.estimate_id+'/edit'") {{ current_event.estimate ? current_event.estimate.service.estimate_number : "" }}
                            a(target="_blank" :href="'/contacts/'+current_event.estimate.service.client_contact_id") {{ current_event.estimate ? current_event.estimate.service.estimate_number : "" }}
                    div(v-if="current_event.service")
                        span.font-weight-bold {{ $t("client.Service") }}:
                        span.ml-1
                            a(target="_blank" :href="'/contacts/'+current_event.service.client_contact_id") {{ $root.service_number(current_event.service) }} - {{current_event.service.name}}
                    div(v-if="current_event.client")
                        div
                            span.font-weight-bold {{ $t("client.Client_name") }}:
                            span.ml-1
                            a(:href="'/companies/'+current_event.client.id") {{ current_event.client.name }}
                        div
                            span.font-weight-bold {{ $t("client.Company_phone") }}:
                            span.ml-1 {{ current_event.client.phone }}
                        div
                            span.font-weight-bold {{ $t("client.Company_email") }}:
                            span.ml-1 {{ current_event.client.email }}
                    div(v-if="current_event.contact")
                        span.font-weight-bold {{ $t("client.Contact") }}:
                        span.ml-1
                            a(:href="'/contacts/'+current_event.contact.id") {{ current_event.contact.name + ' ' + current_event.contact.surname}}
                    div.input-group
                        textarea.form-control(:placeholder="$t('client.comment')" v-model="current_event.comment")
                        div.input-group-prepend
                            button.btn.btn-diga(@click='update_payment_event(current_event.status_id)' style="border-top-right-radius: 1 !important; border-bottom-right-radius: 1 !important;") 
                                i.fa.fa-check
                    button.btn.btn-diga.float-left.mt-3(v-if="current_event.estimate_pay_stage !== null && current_event.estimate_pay_stage.email_template_id !== null" @click='disable_email_auto_send') {{ $t('estimate.Email_auto_send_disable') }}
                    button.btn.btn-success.float-left.mt-3.mr-2(@click='update_payment_event(1)') {{ $t('template.payment_event_paid') }}
                    button.btn.btn-secondary.float-left.mt-3.mr-2(@click='update_payment_event(2)') {{ $t('template.payment_event_waiting_payment') }}
                    button.btn.btn-dark.float-left.mt-3.mr-2(@click='update_payment_event(3)') {{ $t('template.payment_event_needs_attention') }}
                    button.btn.btn-warning.float-left.mt-3.mr-2(@click='update_payment_event(4)') {{ $t('template.payment_event_problem') }}
                    button.btn.btn-default.float-left.mt-3.mr-2(@click='update_payment_event(5)') {{ $t('template.payment_event_canceled') }}
                    button.btn.btn-danger.float-right.mt-3.mr-2(@click='delete_payment_event') {{ $t('gantt.Delete_task') }}
</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';

export default {
    props: ['current_event', 'current_contact'],
    data() {
        return {

        }
    },
    computed: {
        ...mapGetters({
            event_types_by_id: 'getEventTypesById',
            event_groups_by_id: 'getEventGroupsById',
            usersById: 'getUsersById',
        }),
        current_user(){
            return this.usersById[this.current_event.user_id];
        },
        event_title(){
            let title = this.current_event.title;
            title = title.substring(0, title.indexOf(': '));
            return title
        },
    },
    methods: {
        get_event_datetime(datetime){
            return moment(datetime).format('D MMM YYYY');
        },
        timeFormat(datetime) {
            return moment(datetime).format('HH:mm:ss, D MMM YYYY');
        },
        delete_payment_event(){
            if (confirm(this.$t('gantt.Delete_payment_event'))){
                this.$http.delete('/api/payment_events/' + this.current_event.id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$t("template.Error"));
                    } else {
                        $('#calendar').fullCalendar('refetchEvents');
                        $('.fc-day-header').each(function (key11, value11) {
                            // $(value11).prepend('<span class="go-to-this-date"><i class="fa fa-arrow-right"></i></span>');
                            $('.go-to-this-date:nth-child(1)').detach();
                        });
                        $('#calendarModal').modal('hide');
                    }
                }, res => {
                    this.$toastr.e(this.$t("template.Server_error"), this.$t("template.Error"));
                });
            }
        },
        update_payment_event(status_id){
            if (confirm(this.$t('calendar.AreYouSure'))){
                this.$root.global_loading = true;
                this.$http.patch('/api/payment_events/' + this.current_event.id, 
                    {
                        title: this.current_event.name,
                        amount: this.current_event.amount,
                        start: this.current_event.start,
                        description: this.current_event.description,
                        estimate_id: this.current_event.estimate_id,
                        client_id: this.current_event.client_id,
                        contact_id: this.current_event.contact_id,
                        service_id: this.current_event.service_id,
                        status_id: status_id,
                        comment: this.current_event.comment,
                    }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$root.global_loading = false;
                        this.$toastr.e(res.data.errmess, this.$t("template.Error"));
                    } else {
                        this.$root.global_loading = false;
                        $('#calendar').fullCalendar('refetchEvents');
                        $('.fc-day-header').each(function (key11, value11) {
                            // $(value11).prepend('<span class="go-to-this-date"><i class="fa fa-arrow-right"></i></span>');
                            $('.go-to-this-date:nth-child(1)').detach();
                        });
                        $('#calendarModal').modal('hide');
                    }
                }, res => {
                    this.$root.global_loading = false;
                    this.$toastr.e(this.$t("template.Server_error"), this.$t("template.Error"));
                });
            }
        },
        disable_email_auto_send(){
            if (confirm(this.$t('calendar.AreYouSure'))){
                this.$http.post('/api/payment_events/disable_email_sending', {id: this.current_event.id}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$t("template.Error"));
                    } else {
                        location.reload();
                    }
                }, res => {
                    this.$toastr.e(this.$t("template.Server_error"), this.$t("template.Error"));
                });
            }
        }
    },
}
</script>