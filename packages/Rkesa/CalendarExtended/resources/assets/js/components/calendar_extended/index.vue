<style>
    .only-one-group{
        -webkit-border-radius: 5px;
        -moz-border-radius: 5px;
        border-radius: 5px;
        margin: 5px;
    }
    .group-start{
        -webkit-border-top-left-radius: 5px;
        -moz-border-radius-topleft: 5px;
        border-top-left-radius: 5px;
        -webkit-border-top-right-radius: 5px;
        -moz-border-radius-topright: 5px;
        border-top-right-radius: 5px;
        margin: 5px 5px 0;
    }
    .group-end{
        -webkit-border-bottom-left-radius: 5px;
        -moz-border-radius-bottomleft: 5px;
        border-bottom-left-radius: 5px;
        -webkit-border-bottom-right-radius: 5px;
        -moz-border-radius-bottomright: 5px;
        border-bottom-right-radius: 5px;
        margin: 0 5px 5px;
    }
    .group-center{
        margin: 0 5px 0;
    }
    .event-group{
        padding: 5px;
    }
    #calendar .fc-event-container{
        padding: 0;
    }
    .group-header{
        padding: 0 5px;
        color: #ffffff;
    }
    .group-actions{
        float: right;
    }
    .c-pointer{
        cursor: pointer;
    }
</style>

<template lang="pug">
    div.modal.fade#eventGroupModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content(v-if="group_id != null")
                div.modal-header
                    h5.modal-title {{ $t("calendar_extended.Modal_header", {group_name: event_groups_by_id[group_id].name, group_date: group_date}) }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="close_event_modal()")
                        span(aria-hidden="true") &times;
                div.modal-body
                    h6.modal-title {{ $t("calendar_extended.Change_for_all_events") }}
                    fieldset.form-group
                        label.control-label {{ $t("calendar_extended.Event_group") }}
                        select.form-control(v-model="event_group_id")
                            option(value="0") {{ $t("calendar_extended.Dont_need_to_change") }}
                            option(v-for="event_group in event_groups" v-text="event_group.name" v-bind:value="event_group.id")
                    fieldset.form-group
                        label.control-label {{ $t("client.Task_type") }}
                        select.form-control(v-model="event_type_id")
                            option(value="0") {{ $t("calendar_extended.Dont_need_to_change") }}
                            option(v-for="event_type in event_types" v-text="event_type.title" v-bind:value="event_type.id")
                    fieldset.form-group
                        label.control-label {{ $t("client.Responsavel_pela_tarefa") }}
                        select.form-control(v-model="user_id")
                            option(value="0") {{ $t("calendar_extended.Dont_need_to_change") }}
                            template(v-for="user in users.filter(u => u.active === true)")
                                option(v-text="user.name" v-bind:value="user.id")
                div.modal-footer(style="justify-content: space-between;")
                    button.btn.btn-diga(v-on:click="save()") {{ $t("template.Save") }}
                    button.btn(v-on:click="close_event_modal()") {{ $t("template.Cancel") }}
</template>

<script>
import moment from 'moment';
import {mapGetters} from "vuex";

export default {
    data() {
        return {
            group_id: null,
            group_date: null,
            event_type_id: 0,
            event_group_id: 0,
            user_id: 0,
        }
    },
    mounted(){
        this.$bus.$on("calendar_after_event_rendered", this.after_event_rendered);
        this.$bus.$on("calendar_after_all_events_rendered", this.after_all_events_rendered);
    },
    beforeDestroy: function() {
        this.after_event_rendered && this.$bus.$off("calendar_after_event_rendered", this.after_event_rendered);
        this.after_all_events_rendered && this.$bus.$off("calendar_after_all_events_rendered", this.after_all_events_rendered);
    },
    computed: {
        ...mapGetters({
            users: 'getUsers',
            event_types: 'getEventTypes',
            event_groups: 'getEventGroups',
            event_groups_by_id: 'getEventGroupsById',
        }),
    },
    methods: {
        save(){
            this.$http.post('/api/calendar/change_for_group', this.$data).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("calendar_extended.Made_changes_for_group"), this.$root.$t("template.Success"));
                    this.$bus.$emit('calendar_refetch_events');
                    this.close_event_modal();
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        edit_clicked(event){
            this.group_id = event.data.group_id;
            this.group_date = event.data.group_date;
            this.event_type_id = 0;
            this.event_group_id = 0;
            this.user_id = 0;
            jQuery('#eventGroupModal').modal('show');
        },
        close_event_modal(){
            jQuery('#eventGroupModal').modal('hide');
        },
        groupBy(list, keyGetter) {
            const map = new Map();
            list.forEach((item) => {
                const key = keyGetter(item);
                const collection = map.get(key);
                if (!collection) {
                    map.set(key, [item]);
                } else {
                    collection.push(item);
                }
            });
            return map;
        },
        after_event_rendered([event, element, view]){
            element.find('.fc-time').text(moment(event.start).format("mm:ss"));
            element.attr('data-event-date', moment(event.start).format('YYYY-MM-DD'));
            element.attr('data-group-id', event.event_group_id);
        },
        after_all_events_rendered(fc){
            let $this = this;
            const by_dates = this.groupBy($('.fc-event').toArray(), pet => $(pet).attr('data-event-date'));
            by_dates.forEach(function(date){
                const by_groups = $this.groupBy(date, pet => $(pet).attr('data-group-id'));
                by_groups.forEach(function(group){

                    if (group.length == 1){
                        let e = $(group[0]);
                        let group_id = parseInt(e.attr('data-group-id'), 10);
                        if (group_id){
                            let group_date = e.attr('data-event-date');
                            let data = {
                                group_id: group_id,
                                group_date: group_date,
                            };
                            e.wrap("<div class='event-group only-one-group event-group-" + group_id + "'></div>");
                            let edit_el_wrapper = $this.$root.can_do('events', 'update') != 0 ? $("<div class='group-actions'><i class='fa fa-pencil c-pointer'></i></div>").click(data, $this.edit_clicked) : $("<div></div>");
                            let header = $("<div class='group-header'>" + $this.event_groups_by_id[group_id].name + "</div>").append(edit_el_wrapper);
                            e.parent().prepend(header);
                        }
                    } else {
                        let e1 = $(group[0]);
                        let e2 = $(group[group.length - 1]);
                        let group_id = parseInt(e1.attr('data-group-id'), 10);
                        if (group_id){
                            let group_date = e1.attr('data-event-date');
                            let data = {
                                group_id: group_id,
                                group_date: group_date,
                            };
                            e1.wrap("<div class='event-group group-start event-group-" + group_id + "'></div>");
                            let edit_el_wrapper = $this.$root.can_do('events', 'update') != 0 ? $("<div class='group-actions'><i class='fa fa-pencil c-pointer'></i></div>").click(data, $this.edit_clicked) : $("<div></div>");
                            let header = $("<div class='group-header'>" + $this.event_groups_by_id[group_id].name + "</div>").append(edit_el_wrapper);
                            e1.parent().prepend(header);
                            e2.wrap("<div class='event-group group-end event-group-" + group_id + "'></div>");
                            for (let i = 1; i < group.length - 1; i++){
                                let e3 = $(group[i]);
                                e3.wrap("<div class='event-group group-center event-group-" + group_id + "'></div>");
                            }
                        }
                    }
                });
            });
            $('.event-group').each(function(key, value) {
                let group = $(value);
                let td = group.parent();
                let tr = td.parent();
                let tbody = tr.parent();
                let first_tr = tbody.children().first();
                if (tr != first_tr){
                    let column = 0;
                    first_tr.children().each(function(key2, value2){
                        let current_td = $(value2);
                        if (current_td.offset().left == td.offset().left){
                            column = key2 + 1;
                            return true;
                        }
                    });
                    let destination_td = first_tr.find('td:nth-child(' + column + ')');
                    group.appendTo(destination_td);
                }
            });
            $('.fc-widget-content').each(function(key, value){
                let wid = $(value);
                let h = wid.find('.fc-content-skeleton').first().height();
                wid.css('min-height', h);
            });
            fc.dayGrid.segSelector = '.fc-event-container > div > *';
            // fc.dayGrid.bindSegHandlers();
        },
    },
}
</script>