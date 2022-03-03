<template lang="pug">
div.diga-container.p-4
    div
        section.mb-2
            h2 {{ isCreating ? $t('hr.UsersAndGroups_new') : $t('hr.UsersAndGroups_edit') }}
            .row(v-if="currentUserAndGroup")
                section.col-12.col-md-4
                    h6(v-if="isCreating") {{ $t('hr.KPI_UserOrGroup') }}
                    div.row
                        div.col-md-10
                            h4(v-if="!isCreating") {{currentUserAndGroup.group ? currentUserAndGroup.group.name : currentUserAndGroup.user.name}}
                            v-select.w-100.mb-3(:debounce='250', :on-change='user_selected', v-if="!isGroups && isCreating", v-model="selectedUser", :options="usersOptions", :placeholder="$t('template.Search')")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}                
                            v-select.w-100.mb-3(:debounce='250', :on-change='group_selected', v-if="isGroups && isCreating", v-model="selectedGroup", :options="groupsOptions", :placeholder="$t('template.Search')")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                        div.col-md-2
                            div(style="width:120px;", v-if="isCreating")
                                bootstrap-toggle( data-size="mini" v-model="isGroups", :options="{ on: $t('hr.KPI_Group'), off: $t('hr.KPI_User')}", data-width="120", data-height="38", data-onstyle="default")
                    h6 {{ $t('hr.KPI_DateStart') }}
                    date-picker(style="margin-bottom:15px;", format="DD.MM.YYYY", v-model="currentUserAndGroup.start_date", :value-type="$root.valueType", :lang="$root.locale", :first-day-of-week="$root.global_settings.first_day_of_week", :width="'30%'")
                    h6 {{ $t('hr.KPI_Period') }}
                    select.form-control(style="display: inline-block; width: 30%;", v-model="currentUserAndGroup.period_id")
                        option(v-for="period in kpi_periods", :value="period.id") {{ $t('hr.' + period.name) }}
        div.project-section {{ $t('hr.KPI_List') }}
        div.row.mb-2
            div.col-12
                table.table.table-striped(v-if="currentUserAndGroup && currentUserAndGroup.types")
                    thead
                        tr
                            th(style="width: 140px;") #
                            th {{ $t('hr.KPI_type') }}
                            th {{ $t('hr.KPI_type_parameter') }}
                            th {{ $t('hr.KPI_type_plan') }}
                            th {{ $t('template.Remove') }}
                    tbody
                        tr(v-for="(tp,i) in currentUserAndGroup.types")
                            td {{ i + 1 }}
                            td {{ $t('hr.' + getKpiTypesById[tp.type_id].name) }}
                            td
                                select.form-control(v-if="getKpiTypesById[tp.type_id].name=='number_of_finished_tasks_of_special_type' || getKpiTypesById[tp.type_id].name=='number_of_created_tasks_of_special_type'", v-model="tp.additional_params")
                                    option(:value="0") {{ $t('template.Choose') }}
                                    option(v-for="et in eventTypes", :value="et.id") {{ et.title }}
                            td
                                vue-numeric.form-control(v-model="tp.plan_amount", separator=",", v-bind:precision="2")
                            td
                                button.btn.btn-danger(v-on:click="remove_type(tp)") {{$t('template.Remove')}}
                button.btn.btn-diga(v-on:click="show_type_modal()") {{$t('template.Add')}}

        button.btn.btn-diga(v-on:click="save") {{ $t('template.Save') }}

    div.modal.fade#addTypeModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content
                div.modal-body
                    div.form
                        h6 {{$t('hr.KPI_type')}}
                        div.form-group
                            select.form-control(v-model="selectedTypeId")
                                option(:value="0") {{ $t('template.Choose') }}
                                option(v-if="currentUserAndGroup && !currentUserAndGroup.types.some(t => t.type_id === tp.id)", v-for="tp in kpiTypes", :value="tp.id") {{ $t('hr.' + tp.name) }}
                div.modal-footer
                    button.btn.btn-diga(v-on:click="add_type()") {{$t('template.Add')}}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['id'],
    data(){
        return {
            currentUserAndGroup: null,
            isCreating: true,
            usersOptions: null,
            groupsOptions: null,
            isGroups: false,
            kpi_periods: [],
            selectedTypeId: 0,
            selectedUser: null,
            selectedGroup: null
        }
    },
    created(){
        if (this.id) {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.UsersAndGroups_edit');
            this.load_kpi_user_and_group();
        } else {
            document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('hr.UsersAndGroups_new');
            let newUserAndGroup = {
                id: 0,
                user_id: 0,
                group_id: 0,
                period_id: 1,
                start_date: new Date(),
                types: [],
            };
            this.currentUserAndGroup = Object.assign({}, newUserAndGroup);
        }

        this.load_kpi_periods();
        this.get_users_options();
        this.get_groups_options();
    },
    methods: {
        load_kpi_user_and_group(){
            this.$root.global_loading = true;
            this.$http.get('/api/kpi/users_and_groups/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.currentUserAndGroup = res.data;
                    this.currentUserAndGroup.removed_types = [];
                    this.isCreating = false;
                    this.isGroups = this.currentUserAndGroup.user_id === null;
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        load_kpi_periods(){
            this.$http.get('/api/kpi/periods/').then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.kpi_periods = res.data;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
 
        save(){
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$root.global_loading = true;
                if (this.isCreating) {
                    this.$http.post('/api/kpi/users_and_groups/', this.currentUserAndGroup).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Client_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'kpi_users_and_groups'});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                } else {
                    this.$http.patch('/api/kpi/users_and_groups/' + this.id, this.currentUserAndGroup).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.$toastr.s(this.$root.$t("client.Client_saved"), this.$root.$t("template.Success"));
                            this.$router.push({name: 'kpi_users_and_groups'});
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
                }
            });
        },
        remove_type(type){
            if (confirm(this.$root.$t("hr.Sure_remove_type"))){
                if (!('removed_types' in this.currentUserAndGroup)){
                    this.currentUserAndGroup.removed_types = [];
                }
                this.currentUserAndGroup.removed_types.push(type.id);
                let index = this.currentUserAndGroup.types.indexOf(type);
                this.currentUserAndGroup.types.splice(index, 1);
            }
        },

        get_users_options(search, loading){
            this.usersOptions = this.users.map(u => { 
                return {
                    label: u.name,
                    value: u.id
                }
            });
        },
        get_groups_options(search, loading){
            this.groupsOptions = this.groups.map(g => { 
                return {
                    label: g.name,
                    value: g.id
                }
            });
        },

        user_selected(){
            this.currentUserAndGroup.user_id = this.selectedUser.value;
            this.currentUserAndGroup.group_id = 0;
            this.selectedGroup = null;
        },
        group_selected(){
            this.currentUserAndGroup.group_id = this.selectedGroup.value;
            this.currentUserAndGroup.user_id = 0;
            this.selectedUser = null;
        },

        show_type_modal(){
            this.selectedTypeId = 0;
            jQuery('#addTypeModal').modal('show');
        },
        add_type(){
            if (this.selectedTypeId > 0){
                let type = {
                    id: 0,
                    type_id: this.selectedTypeId,
                    additional_params: 0,
                    plan_amount: 0.0,
                };
                this.currentUserAndGroup.types.push(type);
                jQuery('#addTypeModal').modal('hide');
            }
        }
    },
    computed:{
        ...mapGetters({
            users: 'getNotRemovedUsers',
            groups: 'getGroups',
            kpiTypes: 'getKpiTypes',
            getKpiTypesById: 'getKpiTypesById',
            eventTypes: 'getEventTypes'
        })
    }
}
</script>