<style scoped>
.form-check{
    padding-left: unset !important;
}
.diga-container{
    height: unset !important;
}
</style>

<template lang="pug">
    div
        
        div.row
            h2.col-6 {{ $t('template.timetracker_settings') }}
            div.col-6.text-right    
                button.btn.btn-diga(type="button", v-on:click="save" style="margin-bottom: 10px;") {{ $t("template.Save") }}
            div.col-12.mb-3
                div.diga-container.p-4(v-if="data_groups && data_groups.length > 0" :key="componentKey")                    
                    div.row
                        div.col-2 
                            h3 {{$t('hr.KPI_Group')}}
                        div.col-4 
                            h3 {{$t('template.working_hours_for_the_whole_group')}}
                        div.col-6
                            h3 {{$t('template.working_hours_for_the_user')}}
                    div.row.border-bottom.pb-3.my-3(v-for="group in data_groups")
                        div.col-2 {{group.name}}
                        div.col-4.text-center
                            div.text-left
                                div.row
                                    div.col-3
                                        label(:for="'group_s' + group.id") {{$t('estimate.Start_time')}}
                                        TimePicker(:id="'group_s' + group.id" placeholder="" v-model="group.day_start_time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                    div.col-3
                                        label(:for="'group_l' + group.id") {{$t('template.lunch')}}
                                        TimePicker(:id="'group_l' + group.id" placeholder="" v-model="group.lunch_time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                    div.col-3
                                        label(:for="'group_f' + group.id") {{$t('estimate.Finish_time')}}
                                        TimePicker(:id="'group_f' + group.id" placeholder="" v-model="group.day_finish_time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                    div.col-3
                                        p {{$t('estimate.Days')}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group1' + group.id" type="checkbox" value="1" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group1' + group.id") {{$t('template.days_full')[0]}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group2' + group.id" type="checkbox" value="2" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group2' + group.id") {{$t('template.days_full')[1]}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group3' + group.id" type="checkbox" value="3" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group3' + group.id") {{$t('template.days_full')[2]}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group4' + group.id" type="checkbox" value="4" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group4' + group.id") {{$t('template.days_full')[3]}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group5' + group.id" type="checkbox" value="5" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group5' + group.id") {{$t('template.days_full')[4]}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group6' + group.id" type="checkbox" value="6" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group6' + group.id") {{$t('template.days_full')[5]}}
                                        div.form-check
                                            input.form-check-input(:id="'ch_group7' + group.id" type="checkbox" value="7" v-model="group.working_days")
                                            label.form-check-label(:for="'ch_group7' + group.id") {{$t('template.days_full')[6]}}
                            div
                                button.btn.btn-diga(@click="apply_to_users_in_group(group)") {{$t('template.apply_to_all_users_in_group')}}
                        div.col-6.text-center(v-if="data_users && data_users.length > 0")
                            button.btn.btn-diga(v-if="group.show_users === false" v-on:click="show_users(group)") {{$t('template.show')}}
                            button.btn.btn-diga(v-if="group.show_users === true" v-on:click="show_users(group)") {{$t('template.hide')}}
                            div.text-left(v-if="group.show_users === true")
                                div.row.border-bottom.pb-3.my-3(v-for="user in data_users.filter(u => u.group_id === group.id)")
                                    div.col-2 {{user.name}}
                                    div.col-10
                                        div.row
                                            div.col-3
                                                label(:for="'user_s' + user.id") {{$t('estimate.Start_time')}}
                                                TimePicker(:id="'user_s' + user.id" placeholder="" v-model="user.day_start_time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                            div.col-3
                                                label(:for="'user_l' + user.id") {{$t('template.lunch')}}
                                                TimePicker(:id="'user_l' + user.id" placeholder="" v-model="user.lunch_time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                            div.col-3
                                                label(:for="'user_f' + user.id") {{$t('estimate.Finish_time')}}
                                                TimePicker(:id="'user_f' + user.id" placeholder="" v-model="user.day_finish_time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                            div.col-3
                                                p {{$t('estimate.Days')}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user1' + user.id" type="checkbox" value="1" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user1' + user.id") {{$t('template.days_full')[0]}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user2' + user.id" type="checkbox" value="2" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user2' + user.id") {{$t('template.days_full')[1]}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user3' + user.id" type="checkbox" value="3" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user3' + user.id") {{$t('template.days_full')[2]}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user4' + user.id" type="checkbox" value="4" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user4' + user.id") {{$t('template.days_full')[3]}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user5' + user.id" type="checkbox" value="5" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user5' + user.id") {{$t('template.days_full')[4]}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user6' + user.id" type="checkbox" value="6" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user6' + user.id") {{$t('template.days_full')[5]}}
                                                div.form-check
                                                    input.form-check-input(:id="'ch_user7' + user.id" type="checkbox" value="7" v-model="user.working_days")
                                                    label.form-check-label(:for="'ch_user7' + user.id") {{$t('template.days_full')[6]}}

  
                    
        button.btn.btn-diga(v-on:click="save" style="margin-right: 20px;") {{ $t('template.Save') }}
</template>

<script>
import TimePicker from 'element-ui/lib/time-select';

require("element-ui/lib/theme-chalk/index.css");
import {mapGetters} from "vuex";

export default {
    components: {
        TimePicker,
    },
    data() {
        return {
            data_groups: [],
            data_users: [],
            time: null,
            componentKey: 0
        }
    },
    created(){
        this.data_groups = [...this.groups];
        this.data_groups.forEach(g => {
            g.working_days = JSON.parse(g.working_days);
            g.show_users = false;
        });
        this.data_users = [...this.users];
        this.data_users.forEach(g => {
            g.working_days = JSON.parse(g.working_days)
        });
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.timetracker_settings');
        
    },
    computed: {
        ...mapGetters({
            users: 'getNotRemovedUsers',
            usersById: 'getUsersById',
            groups: 'getGroups',
            groupsById: 'getGroupsById',
        }),
    },
    methods: {
        apply_to_users_in_group(group){
            this.data_users.filter(u => u.group_id === group.id).forEach(u => {
                u.day_start_time = group.day_start_time;
                u.lunch_time = group.lunch_time;
                u.day_finish_time = group.day_finish_time;
                u.working_days = group.working_days;
            });
            this.componentKey += 1;
        },
        show_users(group){
            group.show_users = !group.show_users;
            this.componentKey += 1;
        },
        save(){
            this.$root.global_loading = true;
            let payload = {
                data_groups: this.data_groups,
                data_users: this.data_users,
            };

            this.$http.post('/api/timetracker/save_settings', payload).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
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