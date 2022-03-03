<template lang="pug">
    div
        div.row
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('project.Project_types') }}
                    table.referrers-table
                        tr(v-for="(type,i) in types" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="type.name")
                            td
                                button.btn.btn-danger(v-if="type.id > 4 || type.id === 0" v-on:click="remove_type(type)") {{ $t('template.Remove') }}
                    button.btn.btn-diga(v-on:click="add_type()") {{ $t('template.Add') }}
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('project.Project_statuses') }}
                    table.referrers-table.w-100
                        tr(v-for="(status,i) in statuses" style="margin-bottom: 5px;")
                            td
                                | {{ i+1 }}.
                            td
                                input.form-control(v-model="status.name")
                            td.column_autosize
                                button.btn.btn-danger(v-on:click="remove_status(status)") {{ $t('template.Remove') }}
                    button.btn.btn-diga(v-on:click="add_status()") {{ $t('template.Add') }}
        div.row
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('project.Notifications') }}
                    table.table.table-striped
                        thead
                            tr
                                th(style="width: 50%;") {{ $t('project.Type') }}
                                th(style="width: 50%;") {{ $t('project.Recipient') }}
                        tbody
                            tr(v-for="notification in notifications")
                                td
                                    div {{ $t('project.'+notification.type) }}
                                td
                                    div.mb-2.d-flex(v-for="recipient in notification.recipients")
                                        select.form-control(v-model="recipient.type")
                                            option(:value="1") {{ $t('template.Group') }}
                                            option(:value="2") {{ $t('template.Head_of_group') }}
                                            option(:value="3") {{ $t('project.Responsible_of_project') }}
                                            option(:value="4") {{ $t('project.Selected_user') }}
                                        select.form-control.ml-2(v-if="recipient.type == 1 || recipient.type == 2" v-model="recipient.group_id")
                                            option(v-for="group in groups", :value="group.id") {{ group.name }}
                                        select.form-control.ml-2(v-if="recipient.type == 4" v-model="recipient.user_id")
                                            option(v-for="user in users", :value="user.id") {{ user.name }}
                                        button.btn.btn-danger.ml-2(v-on:click="remove_recipient(notification, recipient)")
                                            i.fa.fa-times
                                    button.btn.btn-diga(v-on:click="add_recipient(notification)") {{ $t('template.Add') }}
            div.col-12.col-md-6.mb-3
                div.diga-container.p-4
                    h2 {{ $t('project.Tasks') }}
                    table.table.table-striped
                        thead
                            tr
                                th(style="width: 20%;") {{ $t('project.Event') }}
                                th(style="width: 20%;") {{ $t('project.Task') }}
                                th(style="width: 20%;") {{ $t('project.Date') }}
                                th(style="width: 40%;") {{ $t('project.Responsible') }}
                        tbody
                            template(v-for="autotask in autotasks")
                                tr
                                    td(:rowspan="autotask.recipients.length + 1")
                                        div {{ $t('project.'+autotask.type) }}
                                    td(colspan="3" v-if="autotask.recipients.length == 0")
                                        button.btn.btn-diga(v-on:click="add_task_recipient(autotask)") {{ $t('template.Add') }}
                                tr(v-for="(recipient,i) in autotask.recipients")
                                    td
                                        select.form-control(v-model="recipient.event_type_id")
                                            option(v-for="event_type in event_types", :value="event_type.id") {{ event_type.title }}
                                        template(v-if="i == autotask.recipients.length - 1")
                                            button.btn.btn-diga.mt-2(v-on:click="add_task_recipient(autotask)") {{ $t('template.Add') }}
                                    td
                                        select.form-control(v-model="recipient.event_date")
                                            template(v-if="autotask.type == 'Shipping_date_filled'")
                                                option(:value="1") {{ $t('project.Before_14_to_formed_date') }}
                                            template(v-else)
                                                option(:value="0") {{ $t('project.Current_datetime') }}
                                                option(:value="1") +1 {{ tr($t('template.day'), 1) }}
                                                option(:value="2") +2 {{ tr($t('template.day'), 2) }}
                                                option(:value="3") +3 {{ tr($t('template.day'), 3) }}
                                                option(:value="4") +4 {{ tr($t('template.day'), 4) }}
                                                option(:value="5") +5 {{ tr($t('template.day'), 5) }}
                                                option(:value="6") +6 {{ tr($t('template.day'), 6) }}
                                                option(:value="7") +1 {{ tr($t('template.week'), 1) }}
                                                option(:value="12") +2 {{ tr($t('template.week'), 2) }}
                                                option(:value="13") +3 {{ tr($t('template.week'), 3) }}
                                                option(:value="8") +1 {{ tr($t('template.month'), 1) }}
                                                option(:value="9") +2 {{ tr($t('template.month'), 2) }}
                                                option(:value="10") +6 {{ tr($t('template.month'), 6) }}
                                                option(:value="11") +1 {{ tr($t('template.year'), 1) }}
                                    td
                                        div.d-flex
                                            select.form-control(v-model="recipient.type")
                                                option(:value="2") {{ $t('template.Head_of_group') }}
                                                option(:value="3") {{ $t('project.Responsible_of_project') }}
                                                option(:value="4") {{ $t('project.Selected_user') }}
                                            select.form-control.ml-2(v-if="recipient.type == 2" v-model="recipient.group_id")
                                                option(v-for="group in groups", :value="group.id") {{ group.name }}
                                            select.form-control.ml-2(v-if="recipient.type == 4" v-model="recipient.user_id")
                                                option(v-for="user in users", :value="user.id") {{ user.name }}
                                            button.btn.btn-danger.ml-2(v-on:click="remove_task_recipient(autotask, recipient)")
                                                i.fa.fa-times
        button.btn.btn-diga(v-on:click="save") {{ $t('template.Save') }}
</template>

<script>
import {mapGetters} from "vuex";
import smartPlurals from 'smart-plurals'

export default {
    data(){
        return {
            types: null,
            statuses: null,
            removed_types: [],
            removed_statuses: [],
            loading: false,
            notifications: [],
            autotasks: [],
        }
    },
    created() {
        this.types = JSON.parse(JSON.stringify(this.project_types));
        this.statuses = JSON.parse(JSON.stringify(this.project_statuses));
        this.load_notifications();
        this.load_autotasks();
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.ProjectSettings');
    },
    watch: {
        project_types(){
            this.types = JSON.parse(JSON.stringify(this.project_types));
        },
        project_statuses(){
            this.statuses = JSON.parse(JSON.stringify(this.project_statuses));
        },
    },
    computed: {
        ...mapGetters({
            project_types: 'getProjectTypes',
            groups: 'getGroups',
            users: 'getNotRemovedUsers',
            event_types: 'getEventTypes',
            project_statuses: 'getProjectStatuses',
        }),
    },
    methods: {
        tr(str, amount){
            let lang = this.$root.locale;
            return smartPlurals.Plurals.getRule(lang)(amount, str.split(' | '));
        },
        load_notifications: function(){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/project_notifications').then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                        reject();
                    } else {
                        this.notifications = res.data.notifications;
                        this.$root.global_loading = false;
                        resolve();
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            });
        },
        load_autotasks: function(){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/project_autotasks').then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                        reject();
                    } else {
                        this.autotasks = res.data.autotasks;
                        this.$root.global_loading = false;
                        resolve();
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            });
        },
        add_recipient(notification){
            let newRecipient = {
                id: 0,
                type: 1,
                group_id: this.groups[0].id,
                user_id: this.users[0].id,
            };
            notification.recipients.push(newRecipient);
        },
        remove_recipient(notification, recipient){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_recipient"))){
                if (!('removed_recipients' in notification)){
                    notification.removed_recipients = [];
                }
                notification.removed_recipients.push(recipient.id);
                let index = notification.recipients.indexOf(recipient);
                notification.recipients.splice(index, 1);
            }
        },
        add_task_recipient(autotask){
            let newRecipient = {
                id: 0,
                type: 2,
                group_id: this.groups[0].id,
                user_id: this.users[0].id,
                event_type_id: this.event_types[0].id,
                event_date: 0,
            };
            autotask.recipients.push(newRecipient);
        },
        remove_task_recipient(autotask, recipient){
            if (confirm(this.$root.$t("project.Are_you_sure_you_want_to_delete_the_recipient"))){
                if (!('removed_recipients' in autotask)){
                    autotask.removed_recipients = [];
                }
                autotask.removed_recipients.push(recipient.id);
                let index = autotask.recipients.indexOf(recipient);
                autotask.recipients.splice(index, 1);
            }
        },
        remove_type(type){
            if (confirm(this.$root.$t("project.Are_you_sure_want_to_remove_project_type"))){
                this.removed_types.push(type.id);
                let index = this.types.indexOf(type);
                this.types.splice(index, 1);
            }
        },
        add_type(){
            let type = {
                id: 0,
                name: '',
            };
            this.types.push(type);
        },
        add_status(){
            let status = {
                id: 0,
                name: '',
            };
            this.statuses.push(status);
        },
        remove_status(status){
            if (confirm(this.$root.$t("project.Are_you_sure_want_to_remove_project_status"))){
                this.removed_statuses.push(status.id);
                let index = this.statuses.indexOf(status);
                this.statuses.splice(index, 1);
            }
        },
        save(){
            let payload = JSON.parse(JSON.stringify(this.$data));
            this.$root.global_loading = true;
            this.$http.post('/api/settings/projects', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.load_notifications();
                    this.load_autotasks();
                    this.removed_types = [];
                    this.removed_statuses = [];
                    this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
    },
}
</script>