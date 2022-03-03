<style>
    .timepicker,
    .timepicker-hours,
    .timepicker-minutes{
        margin-top: 20px;
    }
    .text_button{
        cursor: pointer;
        margin-left: 20px;
        font-size: 14px;
    }
</style>

<template lang="pug">
    div.modal.fade#eventCreateModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog.modal-lg(role="document")
            div.modal-content(v-if="event != null")
                div.modal-header
                    h5.modal-title {{ event.id ? $t("gantt.Edit") : $t("template.new_vacation") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_event()")
                        span(aria-hidden="true") &times;
                div.modal-body
                    div.row
                        div.col-12.col-lg-6
                            fieldset.form-group
                                label.control-label(style="text-align: center;width: 100%;") {{ $t("hr.begin_date") }}
                                date-picker(value-type="YYYY-MM-DD" format="YYYY-MM-DD" v-model="event.start", :lang="$root.locale", style="width: 100%", name="start", v-validate="'required'", v-bind:data-vv-as="$t('gantt.Start_date').toLowerCase()")
                                h6.help-block(v-show="errors.has('start')") {{ errors.first('start') }}
                            fieldset.form-group
                                label.control-label(style="text-align: center;width: 100%;") {{ $t("hr.end_date") }}
                                date-picker(value-type="YYYY-MM-DD" format="YYYY-MM-DD" v-model="event.end", :lang="$root.locale", style="width: 100%", name="end", v-validate="'required'", v-bind:data-vv-as="$t('gantt.End_date').toLowerCase()")
                                h6.help-block(v-show="errors.has('end')") {{ errors.first('end') }}
                        div.col-12.col-lg-6
                            fieldset.form-group(v-if="$root.user.can_approve_vacations === true")
                                bootstrap-toggle(v-model="event.is_approved", :options="{ on: $t('template.approve'), off: $t('template.decline')}", data-width="120", data-height="30", data-onstyle="default")
                            fieldset.form-group(:class="{ 'has-error': errors.has('user') }")
                                label.control-label {{ $t("estimate.User") }}
                                select.form-control(:disabled="$root.user.can_approve_vacations !== true" v-model="event.user_id" name="user" v-validate="'not_in:0'")
                                    option(disabled value="0") {{ $t("client.Choose_user") }}
                                    template(v-for="user in users.filter(u => u.active === true)")
                                        option(v-text="user.name" v-bind:value="user.id")
                                span.help-block(v-show="errors.has('user')") {{ errors.first('user') }}

                div.modal-footer(style="justify-content: space-between;")
                    button.btn.grey(v-on:click="cancel_event()") {{ $t("template.Cancel") }}
                    button.btn.btn-danger.float-right(:disabled="event.is_approved === true && $root.user.can_approve_vacations !== true" v-on:click="remove_event()") {{ $t("template.Remove") }}
                    button.btn.btn-diga.float-right(:disabled="event.is_approved === true && $root.user.can_approve_vacations !== true" v-show="!loading" v-on:click="save_event()") {{ $t("template.Save") }}
                    div(v-show="loading" style="float: right;margin-top: 10px;margin-right: 10px;")
                        div.loader.sm-loader
</template>

<script>
import { mapGetters } from 'vuex';
import moment from 'moment';

export default {
    props: ['event'],
    data: function(){
        return {
            loading: false,
        }
    },
    created(){
        let $this = this;
        jQuery(document).keyup(function(e) {
            if (e.keyCode == 27) { 
                $this.cancel_event();
            }
        });
    },
    mounted(){
    },
    methods: {
        remove_event(){
            if (confirm(this.$root.$t('client.Are_you_sure_you_want_to_delete_event'))) {
                this.$http.delete('/api/vacations/' + this.event.id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("client.Event_removed"), this.$root.$t("template.Success"));
                        this.$emit('update');
                        this.cancel_event();
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                });
            }
        },
        cancel_event: function(){
            $('#eventCreateModal').modal('hide');
        },
        date_changed(payload){
            this.$refs.datepicker.$emit('input', payload);
        },
        save_event: function(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }

                var yearDays = this.$root.global_settings.vacation_days_per_year;
                if (!yearDays){
                    yearDays = 24;
                }                
                var totalDays = yearDays + this.users_by_id[this.event.user_id].vacation_days_left;
                var ddays = moment(this.event.end).diff(moment(this.event.start), 'days');

                if (ddays > totalDays){
                    this.$toastr.w(this.$root.$t("template.too_many_days"), this.$root.$t("template.Warning"));
                    return;
                }

                this.loading = true;

                let payload = Object.assign({}, this.event);

                delete payload.source;

                if (this.event.id == null) {
                    this.$http.post('/api/vacations', payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Task_saved"), this.$root.$t("template.Success"));
                            this.$emit('update');
                            this.cancel_event();
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                } else {
                    this.$http.patch('/api/vacations/' + this.event.id, payload).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Task_saved"), this.$root.$t("template.Success"));
                            this.$emit('update');
                            this.cancel_event();
                        }
                        this.loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.loading = false;
                    });
                }
            });
        },
    },
    computed: {
        ...mapGetters({
            users: 'getNotRemovedUsers',
            users_by_id: 'getUsersById',
        }),
    },
    watch: {
        event(val){
        },
    },
}
</script>