<template lang="pug">
div.diga-container.p-4
    h2 {{ $t('estimate.Mao_de_obra') }}
    div.table-responsive
        table.table.table-striped(style='width: 100%;')
            thead
                tr
                    //- td {{ $t('estimate.My_Percent') }}
                    td {{ $root.current_currency.symbol }}{{ $t('estimate.Per_hour') }}
                    td {{ $t('estimate.Days') }}
                    td {{ $t('estimate.Hours') }}
                    td {{ $root.current_currency.symbol }}
            tbody
                tr
                    //- td
                    //-     input.form-control(type='number', min="1", max="100", v-model='currentEstimate.estimate_details.company_percent')
                    td(style='width: 10%;') {{ total_salary }}
                    td(style='width: 20%;')
                        input.form-control(type='number', v-model='currentEstimate.estimate_details.days', style='width: 100%')
                    td(style='width: 10%;')
                        | {{ currentEstimate.estimate_details.days*9 }}
                    td(style='width: 10%;')
                        | {{ total_maodeobra }}

    h3 {{ $t('estimate.Team') }}
    div.table-responsive
        table.table.table-striped(style='width: 100%;')
            thead
                tr
                    td {{$t('estimate.Team')}}
                    td {{$t('estimate.Subcontracting')}}
                    td %
                    td 
            tbody
                tr(v-for="c in e_groups")
                    td
                        button.btn.red.mb-3(v-on:click='delete_estimate_group(c)')
                            i.fa.fa-times
                    td {{c.name}}
                    td
                        bootstrap-toggle(data-size="mini" v-model="c.is_subcontract", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                    td
                        input.form-control(:disabled="!c.is_subcontract" type="number", v-model="c.percent" placeholder="%")
                    td
                        table.table.table-borderless
                            tr(v-for='worker in workers.filter(w => w.group_id === c.id)')
                                td
                                    button.btn.red.mb-3(v-on:click='delete_worker(worker)')
                                        i.fa.fa-times
                                td
                                    template(v-if='worker.search')
                                        v-select.w-100.mb-3(:debounce='250', :on-change='worker_select(worker)', v-model="selectedUser", :options="usersOptions", :placeholder="$t('template.Search')")
                                            template(slot="no-options") {{ $t('template.No_matching_options') }}     
                                    span(v-else='') {{ worker.name }}
                                //- td(style='width: 38px;')
                                //-     button.btn.red(type='button', style='float: right;', v-on:click='delete_worker(worker)')
                                //-         i.fa.fa-times
                                td {{ worker.salary }} {{ $root.current_currency.symbol }}{{ $t('estimate.Per_hour') }}
                            //- tr
                            //-     td(colspan="3")
                            //-         button.btn.green(type='button', v-on:click='add_worker(c.id)') {{ $t('template.Add') }}
                tr
                    td(colspan="3")
                        v-select.w-100.mb-3(:debounce='250', :on-change='team_head_select', v-model="selectedGroup", :options="groupsOptions", :placeholder="$t('template.Search')")
                            template(slot="no-options") {{ $t('template.No_matching_options') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['currentEstimate'],
    data(){
        return {
            selectedGroup: null,
            selectedUser: null,
            workers: [],
            e_groups: [],
        }
    },
    created(){
        this.get_groups_options();
        this.get_users_options();
        if (this.currentEstimate.workers !== null && this.currentEstimate.workers.length > 0){
            this.currentEstimate.workers.forEach(w => {
                let user = {
                    name: this.usersById[w.user_id].name,
                    search: false,
                    salary: w.price,
                    id: w.user_id,
                    group_id: w.group_id
                };
                this.workers.push(user);
            });
        }
        if (this.currentEstimate.groups !== null){
            this.currentEstimate.groups.forEach(g => {
                let group = {
                    id: g.group_id,
                    is_subcontract: g.is_subcontract,
                    percent: g.percent,
                    name: this.groupsById[g.group_id].name
                };
                this.e_groups.push(group);

                this.groupsById[g.group_id].users_ids.forEach(userId => {
                    if (!this.workers.some(w => w.id === userId)){
                        let user = {
                            name: this.usersById[userId].name,
                            search: false,
                            salary: this.usersById[userId].salary,
                            id: userId,
                            group_id: g.group_id
                        };
                        this.workers.push(user);
                    }
                });
            });
        }
        this.$emit('updateWorkers', this.workers);
        this.$emit('updateGroups', this.e_groups);
        if (this.currentEstimate.estimate_details.company_percent === null){
            this.currentEstimate.estimate_details.company_percent = 100;
        }
    },    
    methods:{
        delete_estimate_group(group){
            if (confirm(this.$root.$t("calendar.AreYouSure"))) {
                let i = this.e_groups.indexOf(group);
                this.e_groups.splice(i, 1);

                var newWorkers = this.workers.filter(function(w){
                    return parseInt(w.group_id) !== parseInt(group.id)
                    });
                this.workers = newWorkers;

                this.groupsById[group.id].users_ids.forEach(userId => {
                    this.$emit('delete_estimate_worker', userId);    
                });

                this.$emit('delete_estimate_group', group.id);
                this.$emit('updateWorkers', this.workers);   
                this.$emit('updateGroups', this.e_groups);  
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
        team_head_select(val){
            if (this.selectedGroup && !this.e_groups.some(c => c.id === this.selectedGroup.value)){
                let group = this.groupsById[this.selectedGroup.value];
                let g_group = {
                    name: group.name,
                    id: group.id,
                    percent: 0,
                    is_subcontract: false
                };
                this.e_groups.push(g_group);

                group.users_ids.forEach(userId => {
                    if (!this.workers.some(w => w.id === userId)){
                        let user = {
                            name: this.usersById[userId].name,
                            search: false,
                            salary: this.usersById[userId].salary,
                            id: userId,
                            group_id: group.id
                        };
                        this.workers.push(user);
                    }
                });

                this.$emit('updateWorkers', this.workers);   
                this.$emit('updateGroups', this.e_groups);   
            } 
        },
        //
        delete_worker(worker){
            if (confirm(this.$root.$t("calendar.AreYouSure"))) {
                let i = this.workers.indexOf(worker);
                this.workers.splice(i, 1);
                this.$emit('updateWorkers', this.workers);     
                this.$emit('updateGroups', this.e_groups);
                this.$emit('delete_estimate_worker', worker.id);  
            }   
        },
        add_worker(group_id){
            let user = {
                name: null,
                search: true,
                salary: 0,
                id: null,
                group_id: group_id
            };
            this.workers.unshift(user);   
            this.$emit('updateWorkers', this.workers);              
            this.$emit('updateGroups', this.e_groups);              
        },
        worker_select(val){
            if (this.selectedUser !== null){
                val.salary = this.usersById[this.selectedUser.value].salary;
                val.id = this.selectedUser.value;
                val.name = this.usersById[this.selectedUser.value].name;
                val.search = false;

                this.selectedUser = null;
            }
        },
        round10: function (num){
            return Math.round(num * 100) / 100;
        },
    },
    computed:{
        ...mapGetters({
            groups: 'getGroups',
            users: 'getNotRemovedUsers',
            usersById: 'getUsersById',
            groupsById: 'getGroupsById',
        }),
        total_salary: function(){
            let ts = 0;
            if (this.workers != null) {
                this.workers.forEach(function (user) {
                    ts += user.salary;
                });
            }
            return this.round10(ts);
        },
        total_maodeobra: function(){
            let result = this.round10(this.total_salary * this.currentEstimate.estimate_details.days * 9);
            this.$emit('total', result);
            return result;
        },
    },
    watch: {
        // currentEstimate: {
        //     deep: true,
        //     handler(newEstimate){
        //         let company_percent = newEstimate.estimate_details.company_percent;

        //         if (this.e_groups.length >0){
        //                 let percent_per_company = Math.floor((100 - company_percent) / this.e_groups.length);
        //                 this.e_groups.forEach(g => {
        //                     g.percent = percent_per_company;
        //                 });
        //         }
        //     }            
        // }
    }
}
</script>