<template lang="pug">
div
    div.modal.fade(id="calendarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true")
        div.modal-dialog.modal-lg(role="document")
            div.modal-content(v-if="current_event != null")
                div.modal-header
                    h5.modal-title#exampleModalLabel {{ $t("calendar.Task_info") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                        span(aria-hidden="true") &times;
                div.modal-body
                    div
                        span.font-weight-bold {{ $t("calendar.Task_type") }}:
                        span.ml-1 {{ event_types_by_id[current_event.event_type_id].title }}
                    //- div(v-if="$root.modules.calendar_extended == 1 && current_event.event_group_id")
                    //-     span.font-weight-bold {{ $t("calendar_extended.Event_group") }}:
                    //-     span.ml-1 {{ event_groups_by_id[current_event.event_group_id].name }}
                    div
                        span.font-weight-bold {{ $t("calendar.Date") }}:
                        span.ml-1 {{ get_event_datetime(current_event.start) }}
                    div(v-if="current_event.end")
                        span.font-weight-bold {{ $t("hr.end_date") }}:
                        span.ml-1 {{ get_event_datetime(current_event.end) }}
                    div(v-if="current_event.service")
                        span.font-weight-bold {{ $t("client.Service") }}:
                        span.ml-1 {{ current_event.service.name }}
                    div(v-if="current_event.service && $root.user.can_see_prices")
                        span.font-weight-bold {{ $t("estimate.Preco") }}:
                        span.ml-1 {{ $root.formatFinanceValue(current_event.service.estimate_summ) + ' ' + $root.current_currency.symbol }}
                    div
                        span.font-weight-bold {{ $t("calendar.Description") }}:
                        span.ml-1 {{ current_event.description }}
                    div
                        span.font-weight-bold {{ $t("calendar.Responsible_user") }}:
                        span.ml-1 {{ current_user.name }}
                    div(v-if="current_event.project")
                        span.font-weight-bold {{ $t("calendar.Project") }}:
                        span.ml-1
                            a(:href="'/projects/'+current_event.project.id") {{ current_event.project.name }}
                    div(v-if="current_event.project_url")
                        span.font-weight-bold {{ $t("calendar.Url") }}:
                        span.ml-1
                            a(:href="current_event.project_url", target="_blank") {{ current_event.project_url }}
                    div(v-if="current_contact")
                        span.font-weight-bold {{ $t("calendar.Name") }}:
                        span.ml-1 {{ $root.fullName(current_contact) }}
                    div(v-if="current_contact")
                        span.font-weight-bold {{ $t("calendar.Email") }}:
                        span.ml-1 {{ get_emails(current_contact) }}
                    div(v-if="current_contact")
                        span.font-weight-bold {{ $t("calendar.Phone") }}:
                        span.ml-1 {{ get_phones(current_contact) }}
                    div(v-if="current_event.service")
                        span.font-weight-bold {{ $t("calendar.Address") }}:
                        span.ml-1 {{ current_event.service.address }}
                div.modal-footer
                    button.btn-diga.btn-sm.float-right(v-if="current_event.done === 0" v-on:click="done_event()")
                        i.fa.fa-check-circle-o.mr-2
                        | {{$t("calendar.Finish")}}
                    button.btn-diga.btn-sm.float-right(v-on:click="edit_event()")
                        | {{$t("client.Edit_task")}}
                    router-link.btn-diga.btn-sm.float-right(v-if="current_event.client_contact && current_event.client_contact.can_be_readed", :to="{name: 'client_show', params: {id: current_contact.id}}" target="_blank")
                        i.fa.fa-user.mr-2
                        | {{ $t("calendar.Open_client_card") }}
    edit-form(
        :event="current_event",
        :action="action",
        :services="services",
        :projects="projects",
        v-on:remove_event="remove_current",
        v-on:cancel="cancel_current"
        v-on:save="save_current",
        v-on:update="update_current")
</template>

<script>
import {mapGetters} from "vuex";
import moment from 'moment';
import edit_form from '../../../../../../Client/resources/assets/js/components/card/events/_form.vue';

export default {
    components: {
        'edit-form': edit_form,
    },
    props: ['current_event', 'current_contact'],
    data() {
        return {
            // current_event: null,
            action: null,
            services: [],
            projects: []
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
    },
    methods: {
        get_event_datetime(datetime){
            if (this.$root.modules.calendar_extended == 1){
                return moment(datetime).format('mm:ss:00, D MMM YYYY');
            } else {
                return this.timeFormat(datetime);
            }
        },
        timeFormat(datetime) {
            return moment(datetime).format('HH:mm:ss, D MMM YYYY');
        },
        get_phones(contact){
            return contact.client_contact_phones.map(function(p){ return p.phone_number }).join(', ');
        },
        get_emails(contact){
            return contact.client_contact_emails.map(function(p){ return p.email }).join(', ');
        },

        edit_event: function(event){
            // if (!('instead_done' in event)){
            //     event.instead_done = null;
            // }
            // this.old_event = Object.assign({}, event);
            // this.current_event = event;
            if (this.current_event.service){
                this.services.push(this.current_event.service);
            }
            if (this.current_event.project){
                this.projects.push(this.current_event.project);
            }            
            jQuery('#eventModal').modal('show');
        },
        done_event: function(event){
            if (confirm(this.$root.$t('client.Are_you_sure_finish_task'))) {
                this.$root.global_loading = true;
                this.$http.post('/api/calendar/' + this.current_event.id + '/finish').then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Task_finished"), this.$root.$t("template.Success"));
                        // add to history
                        this.$bus.$emit('system_message', res.data.history);
                        //event.done = true;
                        // location.reload();
                        this.$emit('update');
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            }
        },
        remove_current: function(){            
            // let index = this.my_events.indexOf(this.current_event);
            // this.my_events.splice(index, 1);
            // this.current_event = null;
            this.$emit('update');
            jQuery('#eventModal').modal('hide');
            // location.reload();
        },
        cancel_current: function(){
            jQuery('#eventModal').modal('hide');
        },
        save_current: function(){
            this.my_events.push(this.current_event);
            let $this = this;
            jQuery('#eventModal').modal('hide').on('hidden.bs.modal', function(){
                if ($this.action){
                    $this.$bus.$emit('action_event_ok', $this.current_event.id, $this.current_event.start, $this.current_event.description);
                }
                $this.current_event = null;
                $this.old_event = null;
                $this.action = null;
            });
        },
        update_current: function(){
            this.$emit('update');
            jQuery('#eventModal').modal('hide');
            // location.reload();
            // this.current_event = null;
            // this.old_event = null;            
        },
    },
}
</script>