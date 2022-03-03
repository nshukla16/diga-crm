<style>
    table#estimate_new_table > tbody > tr > td{
        border-right: 1px solid #CCC;
        border-bottom: 1px solid #CCC;
        height: 22px;
        empty-cells: show;
        line-height: 21px;
        background-color: #FFF;
        vertical-align: top;
        outline-width: 0;
        background-clip: padding-box;
        padding: 5px;
    }
    /**/
    table.table-invisible-borders{
        width: 100%;
    }
    table.table-invisible-borders td{
        border: none;
        padding: 0;
    }
    table.table-invisible-borders tr:first-child td {
        border: none;
    }
    table.table-invisible-borders td:first-of-type{
        border: none;
    }
    /**/
    table#estimate_new_table textarea{
        border: 1px solid #a9a9a9;
    }
    table#estimate_new_table select{
        height: 25px;
        background-color: white;
    }
    table#estimate_new_table .category{
        font-weight: bold;
    }
    table#estimate_new_table .actions{
        text-align: center;
        vertical-align: middle;
    }
    table#estimate_new_table .actions i{
        cursor: pointer;
    }
    table#estimate_new_table .change-order{
        height: 10px;
        display: block;
    }
    table#estimate_new_table .separator,
    table#estimate_new_table .total{
        text-align: center;
    }
    table#estimate_new_table .active td{
        background-color: #eee;
    }
    table#estimate_new_table thead td{
        padding: 5px;
    }
    table#estimate_new_table textarea {
        width: 100%;
        -webkit-box-sizing: border-box; /* <=iOS4, <= Android  2.3 */
        -moz-box-sizing: border-box; /* FF1+ */
        box-sizing: border-box; /* Chrome, IE8, Opera, Safari 5.1*/
    }
    table#estimate_new_table > tbody > tr:first-child > td {
        border-top: 1px solid #CCC;
    }
    table#estimate_new_table > tbody > tr > td:first-of-type{
        border-left: 1px solid #CCC;
    }
    .head td{
        background-color: #eee;
    }
    .teammates td{
        height: 45px;
    }
    .map-container {
        width: 500px;
        height: 300px;
        max-width: 100%;
    }
    /*.dropdown.v-select ul.dropdown-menu{*/
        /*z-index: 99999;*/
    /*}*/
</style>

<template lang="pug">
div
    #planning-edit(v-if="currentEstimate")
        .row.mb-3
            .col-12
                button.btn.btn-diga(type='button', v-on:click='update_planning()') {{ $t('template.Save') }}
                button.btn.btn-diga.float-right(style="margin-left: 15px;", type='button', v-on:click='createGanttChart()') {{ $t('template.GenerateGanttChart') }}
                router-link.btn.btn-diga.float-right(v-if='currentEstimate.service.client_contact_id', :to="{name: this.$root.contact_or_client_show_route(), params: {id: currentEstimate.service.client_contact_id }}") {{ $t('estimate.Open_client_card') }}
        .row
            .col-12.col-md-6.mb-3
                div.row.mb-3
                    div.col-12
                        general(:currentEstimate="currentEstimate")
                div.row.mb-3
                    div.col-12
                        results(:currentEstimate="currentEstimate" 
                            :total_maodeobra="total_maodeobra" 
                            :total_material="total_material" 
                            :price="currentEstimate.price"
                            :total_dislocation="total_dislocation")
                div.row
                    div.col-12
                        transportation(:currentEstimate="currentEstimate" @total="updateTransportationTotal")
            .col-12.col-md-6.mb-3
                div.row.mb-3
                    div.col-12
                        workers(:currentEstimate="currentEstimate" @delete_estimate_group="delete_estimate_group" @delete_estimate_worker="delete_estimate_worker" @total="updateWorkersTotal" @updateWorkers="updateWorkers" @updateGroups="updateGroups")
                div.row
                    div.col-12
                        materials(:currentEstimate="currentEstimate" :lines="lines" @total="updateMaterialsTotal" @updateMaterials="updateMaterials")
        .row
            .col-12
                div.diga-container.p-4
                    div.table-responsive
                        table#estimate_new_table(style='width: 100%;')
                            thead
                                tr
                                    td Nº
                                    td(style="width: 30%;") {{ $t('estimate.Description') }}
                                    td {{ $t('estimate.Un') }}
                                    td {{ $t('estimate.Quantity') }}
                                    td(style="width: 20%;") {{ $t('estimate.Notes') }}
                                    td(style='width: 40%;') {{ $t('estimate.Mao_de_obra') + ' / ' + $t('estimate.Hours') }}
                            tbody
                                // FIRST LEVEL
                                template(v-for="(line1, index1) in lines")
                                    template(v-if="is_category_or_subcategory(line1)")
                                        tr.category
                                            td {{ line1.line_number }}
                                            td(colspan='5') {{ line1.category_name }}
                                    template(v-if="is_separator(line1)")
                                        tr
                                            td.separator(colspan='6') {{ line1.separator_name }}
                                    // SECOND LEVEL
                                    template(v-for='(line2, index2) in line1.children')
                                        template(v-if="is_category_or_subcategory(line2)")
                                            tr
                                                td {{ line2.line_number }}
                                                td(colspan='5') {{ line2.category_name }}
                                        template(v-if="is_data_or_subdata(line2)")
                                            tr
                                                td {{ line2.line_number }}
                                                td {{ line2.data_description }}
                                                td(v-html='unitize(line2.data_measure)')
                                                td {{ line2.data_quantity }}
                                                td {{ line2.data_note }}
                                                td
                                                    table.table-invisible-borders
                                                        tbody
                                                            tr(v-for='worker in line2.workers')
                                                                td.pr-2
                                                                    select.form-control.mb-3(v-model='worker.user_id' style="height: 100%")
                                                                        option(disabled="disabled" value="null") {{$t('estimate.Enter_name')}}
                                                                        option(v-for="w in workers" :value="w.id") {{w.name}}
                                                                    //- v-select.mb-3(:debounce='250', v-model='worker.user_id', :on-search='get_line_workers_options', :on-change='line_worker_select(worker)', :options='line_workers', :placeholder="$t('estimate.Enter_name')")
                                                                    //-     template(slot="no-options") {{ $t('template.No_matching_options') }}
                                                                td.pr-2
                                                                    input.form-control.mb-3(v-model="worker.hours")
                                                                td(style='width:37px;')
                                                                    button.btn.red.mb-3(v-on:click='delete_line_worker(worker,line2)')
                                                                        i.fa.fa-times
                                                    button.btn.green(type='button', v-on:click='add_line_worker(line2)') {{ $t('template.Add') }}
                                                    button.btn.green.ml-3(type='button', v-on:click='add_line_all_workers(line2)') {{ $t('estimate.Add_all') }}
                                        template(v-if="is_ficha(line2)")
                                            tr
                                                td {{ line2.line_number }}
                                                td {{ line2.ficha_description }}
                                                td(v-html='unitize(line2.ficha_measure)')
                                                td {{ line2.ficha_quantity }}
                                                td {{ line2.ficha_note }}
                                                td
                                                    table.table-invisible-borders
                                                        tbody
                                                            tr(v-for='worker in line2.workers')
                                                                td.pr-2
                                                                    select.form-control.mb-3(v-model='worker.user_id' style="height: 100%")
                                                                        option(disabled="disabled" value="null") {{$t('estimate.Enter_name')}}
                                                                        option(v-for="w in workers" :value="w.id") {{w.name}}
                                                                td.pr-2
                                                                    input.form-control.mb-3(v-model="worker.hours")
                                                                td(style='width:37px;')
                                                                    button.btn.red.mb-3(v-on:click='delete_line_worker(worker,line2)')
                                                                        i.fa.fa-times
                                                    button.btn.green(type='button', v-on:click='add_line_worker(line2)') {{ $t('template.Add') }}
                                                    button.btn.green.ml-3(type='button', v-on:click='add_line_all_workers(line2)') {{ $t('estimate.Add_all') }}
                                        template(v-if="is_separator(line2)")
                                            tr
                                                td.separator(colspan='6') {{ line2.separator_name }}
                                        // THIRD LEVEL
                                        template(v-for='(line3, index3) in line2.children')
                                            template(v-if="is_data_or_subdata(line3)")
                                                tr
                                                    td {{ line3.line_number }}
                                                    td {{ line3.data_description }}
                                                    td(v-html='unitize(line3.data_measure)')
                                                    td {{ line3.data_quantity }}
                                                    td {{ line3.data_note }}
                                                    td
                                                        table.table-invisible-borders
                                                            tbody
                                                                tr(v-for='worker in line3.workers')
                                                                    td.pr-2
                                                                        select.form-control.mb-3(v-model='worker.user_id' style="height: 100%")
                                                                            option(disabled="disabled" value="null") {{$t('estimate.Enter_name')}}
                                                                            option(v-for="w in workers" :value="w.id") {{w.name}}
                                                                        //- v-select.mb-3(:debounce='250', v-model='worker.user_id', :on-search='get_line_workers_options', :on-change='line_worker_select(worker)', :options='line_workers', :placeholder="$t('estimate.Enter_name')")
                                                                        //-     template(slot="no-options") {{ $t('template.No_matching_options') }}
                                                                    td.pr-2
                                                                        input.form-control.mb-3(v-model="worker.hours")
                                                                    td(style='width:37px;')
                                                                        button.btn.red.mb-3(v-on:click='delete_line_worker(worker,line3)')
                                                                            i.fa.fa-times
                                                        button.btn.green(type='button', v-on:click='add_line_worker(line3)') {{ $t('template.Add') }}
                                                        button.btn.green.ml-3(type='button', v-on:click='add_line_all_workers(line3)') {{ $t('estimate.Add_all') }}
                                            template(v-if="is_ficha(line3)")
                                                tr
                                                    td {{ line3.line_number }}
                                                    td {{ line3.ficha_description }}
                                                    td(v-html='unitize(line3.ficha_measure)')
                                                    td {{ line3.ficha_quantity }}
                                                    td {{ line3.ficha_note }}
                                                    td
                                                        table.table-invisible-borders
                                                            tbody
                                                                tr(v-for='worker in line3.workers')
                                                                    td.pr-2
                                                                        select.form-control.mb-3(v-model='worker.user_id' style="height: 100%")
                                                                            option(disabled="disabled" value="null") {{$t('estimate.Enter_name')}}
                                                                            option(v-for="w in workers" :value="w.id") {{w.name}}
                                                                    td.pr-2
                                                                        input.form-control.mb-3(v-model="worker.hours")
                                                                    td(style='width:37px;')
                                                                        button.btn.red.mb-3(v-on:click='delete_line_worker(worker,line3)')
                                                                            i.fa.fa-times
                                                        button.btn.green(type='button', v-on:click='add_line_worker(line3)') {{ $t('template.Add') }}
                                                        button.btn.green.ml-3(type='button', v-on:click='add_line_all_workers(line3)') {{ $t('estimate.Add_all') }}
                                            template(v-if="is_separator(line3)")
                                                tr
                                                    td.separator(colspan='6') {{ line3.separator_name }}
                                            // FOURTH LEVEL
                                            template(v-for='(line4, index4) in line3.children')
                                                template(v-if="is_data_or_subdata(line4)")
                                                    tr
                                                        td {{ line4.line_number }}
                                                        td {{ line4.data_description }}
                                                        td(v-html='unitize(line4.data_measure)')
                                                        td {{ line4.data_quantity }}
                                                        td {{ line4.data_note }}
                                                        td
                                                            table.table-invisible-borders
                                                                tbody
                                                                    tr(v-for='worker in line4.workers')
                                                                        td.pr-2
                                                                            select.form-control.mb-3(v-model='worker.user_id' style="height: 100%")
                                                                                option(disabled="disabled" value="null") {{$t('estimate.Enter_name')}}
                                                                                option(v-for="w in workers" :value="w.id") {{w.name}}
                                                                        td.pr-2
                                                                            input.form-control.mb-3(v-model="worker.hours")
                                                                        td(style='width:37px;')
                                                                            button.btn.red.mb-3(v-on:click='delete_line_worker(worker,line4)')
                                                                                i.fa.fa-times
                                                            button.btn.green(type='button', v-on:click='add_line_worker(line4)') {{ $t('template.Add') }}
                                                            button.btn.green.ml-3(type='button', v-on:click='add_line_all_workers(line4)') {{ $t('estimate.Add_all') }}
                                                template(v-if="is_separator(line4)")
                                                    tr
                                                        td.separator(colspan='6') {{ line4.separator_name }}
                                    template(v-if="is_category_or_subcategory(line1)")
                                        tr
                                            td(colspan='6')
        .row.mt-3
            .col-12
                button.btn.btn-diga(type='button', v-on:click='update_planning()') {{ $t('template.Save') }}
                button.btn.btn-diga.float-right(style="margin-left: 15px;", type='button', v-on:click='createGanttChart()') {{ $t('template.GenerateGanttChart') }}
                router-link.btn.btn-diga.float-right(v-if='currentEstimate.service.client_contact_id', :to="{name: this.$root.contact_or_client_show_route(), params: {id: currentEstimate.service.client_contact_id }}") {{ $t('estimate.Open_client_card') }}
    
    div.modal.fade#newPlanModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content
                div.modal-header
                    h5.modal-title {{ $t("gantt.Plan_new") }}
                    button(type="button" class="close" data-dismiss="modal" aria-label="Close" v-on:click="cancel_new_plan()")
                        span(aria-hidden="true") &times;
                div.modal-body
                    fieldset.form-group(:class="{ 'has-error': errors.has('task_name') }")
                        label {{ $t('project.Name') }}
                        input.form-control.mr-2(v-model="estimate_name")
                    fieldset.form-group
                        label {{ $t('project.Initial_date') }}
                        date-picker.form-group(style="margin-bottom:15px; margin-left: 10px", format="DD.MM.YYYY", v-model="estimate_date_start", :value-type="$root.valueType", :lang="$root.locale", :first-day-of-week="$root.global_settings.first_day_of_week", :width="'30%'")
                    div.text-center.mt-3
                        button.btn.btn-diga(v-on:click="create_plan") {{ $t('template.Create') }}
</template>

<script>
import {mapGetters} from "vuex";
import general from "./general.vue";
import workers from "./workers.vue";
import materials from "./materials.vue";
import transportation from "./transportation.vue";
import results from "./results.vue";
import moment from 'moment';

export default {
    components: {
        general,
        workers,
        materials,
        transportation,
        results
    },
    data: function (){
        return {
            timer: '',
            total_maodeobra: 0,
            total_maodeobra_general: 0,
            total_material: 0,
            total_dislocation: 0,
            currentEstimate: null,
            lines: [],
            days: 1,
            workers: [],
            materials: [],
            estimate_name: '',
            estimate_date_start: null,  
            groups: [],
            deleted_groups: [],
        }
    },
    props: ['id'],
    created() {
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('estimate.Edit_planning');
        let $this = this;
        this.$http.get('/api/estimates/' + this.id).then(res => {
            //                this.currentEstimate = {};
            Vue.set(this, 'currentEstimate', res.data);
            //                Object.assign(this.currentEstimate, res.data);
            this.lines = this.currentEstimate.lines;
            this.generate_tree();
            this.days = this.currentEstimate.deadline;
            if (this.currentEstimate.estimate_details === null){
                this.currentEstimate.estimate_details = {
                    estimate_id: this.currentEstimate.id,
                    days: this.days,
                    start_point_lat: 38.7386218,
                    start_point_lng: -9.1249667,
                    consumption_per_100_km: 7.5,
                    gasoline_price: 1.25
                };
            }
        }, res => {
            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
        });

        this.estimate_date_start = moment().add(1, 'days');
        if (this.estimate_date_start.weekday() === 7){
            this.estimate_date_start = moment().add(2, 'days');
        }
        this.timer = setInterval(this.update_planning, 60000);
    },
    beforeDestroy() {
        clearInterval(this.timer);
        if (confirm(this.$root.$t('estimate.Are_you_sure_close_estimate_planning'))) {
            this.update_planning();
        }
    },
    mounted() {
        let $this = this;
    },
    methods: {
        delete_estimate_group(value){
            this.deleted_groups.push(value);
        },
        delete_estimate_worker(value){
            this.delete_estimate_worker_recursive(this.lines, value);
        },
        delete_estimate_worker_recursive(d_lines, user_id){
            d_lines.forEach(l => {
                if (l.workers.length > 0){
                    let i = l.workers.map(function(e) { return e.user_id; }).indexOf(user_id);
                    l.workers.splice(i, 1);
                }
                if (l.children.length > 0){
                    this.delete_estimate_worker_recursive(l.children, user_id);
                }
            });
        },
        updateGroups(value){
            this.groups = value;
        },
        updateWorkersTotal(value){            
            this.total_maodeobra_general = value; 
            var value_from_lines = this.workers_in_lines_spends();

            if (value_from_lines > 0){
                this.total_maodeobra = value_from_lines;
            }
            else{
                this.total_maodeobra = value;
            }
        },
        updateMaterialsTotal(value){
            this.total_material = value;
        },
        updateTransportationTotal(value){
            this.total_dislocation = value;
        },
        updateWorkers(value){
            this.workers = value;
            if (this.workers.length > 0 && this.lines.length > 0 /*&& this.is_any_workers_recursive(this.lines) === false*/){
                // this.add_workers_to_all_lines();
            }
        },
        is_any_workers_recursive(d_lines){
            d_lines.forEach(l => {
                if (l.workers.length > 0){
                    return true;
                }
                if (l.children.length > 0){
                    if (this.is_any_workers_recursive(l.children) === true){
                        return true;
                    }
                }
            });

            return false;
        },
        updateMaterials(value){
            this.materials = value;
        },
        createGanttChart(){
            jQuery('#newPlanModal').modal('show');
        },
        cancel_new_plan(){
            jQuery('#newPlanModal').modal('hide');
        },
        create_plan(){
			this.$root.global_loading = true;
            this.$validator.validate().then(result => {
                if (!result) {
                    this.$toastr.w(this.$t("template.Need_to_fill"), this.$t("template.Warning"));
                    return;
                }
	            this.$http.post('/api/estimate_plannings', {
	                estimate_id: this.currentEstimate.id,
	                name: this.estimate_name,
                    is_custom: 0,
                    estimate_date_start: this.estimate_date_start,
	            }).then(res => {
                    if (res.data.errcode == 1) {
						this.$root.global_loading = false;
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
						this.$root.global_loading = false;
                        jQuery('#newPlanModal').modal('hide');
                        this.$router.push({
                            name: 'estimate_planning_show',
                            params: {id: res.data.id}
                        });
                    }
                }, res => {
                	this.$root.global_loading = false;
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            });
        },
        // line workers
        add_line_worker(line){
            if (!line.workers){
                line.workers = [];
            }
            if (this.workers.length > 0){
                let worker = {
                    user_id: null,
                    hours: 0,
                    salary: 0
                };
                line.workers.push(worker);
            } else {
                this.$toastr.w(this.$root.$t("estimate.Need_to_choose_team"), this.$root.$t("template.Warning"));
            }
        },
        add_line_all_workers(line){
            if (line.workers.length  === 0){
                line.workers = [];

                if (this.workers.length > 0){
                    var hours_for_worker = 0;

                    var measure_id = line.data_measure || line.ficha_measure;
                    var quantity = line.ficha_quantity || line.data_quantity;

                    if (measure_id !== null && quantity !== null){
                        let unit = this.unitsById[measure_id];
                        if (unit.hours_to_do !== null){
                            let n = unit.hours_to_do * quantity;
                            hours_for_worker = Math.ceil(n / this.workers.length);
                        }
                    }
                
                    let workers_d = this.workers.slice();
                    workers_d.forEach(function(user, i){
                        let worker = {
                            user_id: user.id,
                            hours: hours_for_worker,
                            salary: user.salary
                        };
                        line.workers.push(worker);
                    });
                } else {
                    this.$toastr.w(this.$root.$t("estimate.Need_to_choose_team"), this.$root.$t("template.Warning"));
                }
            }else{
                    if (this.workers.length > 0){
                    var hours_for_worker = 0;

                    let workers_d = this.workers.slice();
                    workers_d.forEach(function(user, i){
                        if (!line.workers.some(w => w.user_id === user.id)){  
                            let worker = {
                                user_id: user.id,
                                hours: hours_for_worker,
                                salary: user.salary
                            };
                            line.workers.push(worker);
                        }
                    });
                } else {
                    this.$toastr.w(this.$root.$t("estimate.Need_to_choose_team"), this.$root.$t("template.Warning"));
                }
            }
        },
        add_workers_to_all_lines(){
            this.recursive_add_workers_to_lines(this.lines);
        },
        recursive_add_workers_to_lines(i_lines){
            i_lines.forEach(l => {
                this.add_line_all_workers(l);
                this.recursive_add_workers_to_lines(l.children);
            });
        },
        delete_line_worker(worker, line){
            let $i = line.workers.indexOf(worker);
            line.workers.splice($i, 1);
        },
        //
        check_for_unfilled_estimate_line_workers: function (node){
            let err = false;
            let $this = this;
            if (node == null){
                this.lines.forEach(function(line){
                    if ($this.check_for_unfilled_estimate_line_workers(line)){
                        err = true;
                    }
                });
            } else {
                if ($this.is_data_or_subdata(node) || $this.is_ficha(node)){
                    node.workers.forEach(function(e){ if (e.id == null){ err = true; } });
                }
                node.children.forEach(function(child){
                    if ($this.check_for_unfilled_estimate_line_workers(child)){
                        err = true;
                    }
                });
            }
            return err;
        },
        update_planning: function () {
            this.$root.global_loading = true;
            let err = false;
            //this.materials.forEach(function(e){ if (!e.resource_id){ err = true; } });
            // if (this.total_material === 0) {err = true;}
            if (err) {
                this.$root.global_loading = false;
                this.$toastr.w(this.$root.$t("estimate.Need_to_select_material"), this.$root.$t("template.Warning"));
            } else {
                // if (this.head) {
                //     this.head.users.forEach(function (e) { if (e.id == null) { err = true; } });
                // }
                // if (this.total_maodeobra === 0){err = true;}
                if (err) {
                    this.$root.global_loading = false;
                    this.$toastr.w(this.$root.$t("estimate.Need_to_select_worker"), this.$root.$t("template.Warning"));
                } else {
                    // let err = this.check_for_unfilled_estimate_line_workers();
                    // if (err) {
                    //     this.$toastr.w(this.$root.$t("estimate.Need_to_select_worker_line"), this.$root.$t("template.Warning"));
                    // } else {
                        let payload = Object.assign({}, this.$data);
                        payload.lines_workers = this.generate_line_workers_array(this.lines);
                        payload.estimate_details = this.currentEstimate.estimate_details;
                        payload.lines = null;
                        payload.current_active = null;
                        payload.current_show_insert_after = null;
                        payload.current_ficha = null;
                        payload.routes = null;
                        payload.currentEstimate = null;
                        payload.groups = this.groups;

                        // if (this.head != null) {
                        //     payload.workers = [];
                        //     this.head.users.forEach(function (u) {
                        //         payload.workers.push({id: u.id, price: u.salary});
                        //     });
                        //     payload.head = null;
                        // }

                        let $this = this;
                        this.$http.patch('/api/plannings/' + this.id.toString(), payload, {method: 'PATCH'}).then(res => {
                            this.$root.global_loading = false;
                            if (res.data.errcode != 1) {
                                $this.$toastr.s($this.$root.$t("estimate.Planning_saved"), $this.$root.$t("template.Success"));
                                //window.location.reload(true);
                            } else {
                                $this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            }
                        }, response => {
                            this.$root.global_loading = false;
                            $this.$toastr.e(this.$root.$t("template.Server_error"), $this.$root.$t("template.Error"));
                        });
                    //}
                }
            }
        },
        generate_line_workers_array: function (element) {
            let my_array = [];
            if (Array.isArray(element)){
                for (var i = 0; i < element.length; i++) {
                    my_array = my_array.concat(this.generate_line_workers_array(element[i]));
                }
            } else {
                if (element.workers != null && element.workers.length > 0) {
                    let new_element = {};
                    new_element.id = element.id;
                    new_element.workers = element.workers;
                    my_array.push(new_element);
                }
                if ('children' in element) {
                    for (var j = 0; j < element.children.length; j++) {
                        my_array = my_array.concat(this.generate_line_workers_array(element.children[j]));
                    }
                }
            }
            return my_array;
        },
        // Helpers
        generate_tree: function () {
            var map = {};
            var line;
            var roots = [];
            for (var i = 0; i < this.lines.length; i += 1) {
                line = this.lines[i];
                Vue.set(line, 'children', []);
                map[line.id] = i;
                if (line.parent_id !== null) {
                    this.lines[map[line.parent_id]].children.push(line);
                    line.parent = this.lines[map[line.parent_id]];
                } else {
                    roots.push(line);
                    line.parent = null;
                }
            }
            this.lines = roots;
        },
        generate_line_number: function (element) {
            let $order = 0;
            if (element.parent !== null) {
                let $par = element.parent;
                for (var i = 0; i < element.parent.children.length; i++) {
                    if (!this.is_separator($par.children[i])) {
                        $order++;
                    }
                    if ($par.children[i] === element) {
                        break;
                    }
                }
            } else {
                for (var j = 0; j < this.lines.length; j++) {
                    if (!this.is_separator(this.lines[j])) {
                        $order++;
                    }
                    if (this.lines[j] === element) {
                        break;
                    }
                }
            }
            if (element.parent !== null) {
                element.line_number = element.parent.line_number + '.' + $order;
            } else {
                element.line_number = $order;
            }
            let $this = this;
            if ('children' in element) {
                element.children.forEach(function (item) {
                    $this.generate_line_number(item);
                });
            }
        },
        unitize: function (value) {
            if (value != null) {
                let $un = this.unitsById[value].measure;
                switch ($un) {
                case 'm2':
                    $un = "m<sup>2</sup>";
                    break;
                case 'm3':
                    $un = "m<sup>3</sup>";
                    break;
                case 'm2/ml':
                    $un = "m<sup>2</sup>/ml";
                    break;
                }
                return $un;
            } else {
                return "";
            }
        },
        // Checks
        is_category_or_subcategory: function (element) {
            return element.lineable_type === "\\App\\EstimateLineCategory"
        },
        is_category: function (element) {
            return element.lineable_type === "\\App\\EstimateLineCategory" && element.parent === null
        },
        is_subcategory: function (element) {
            return element.lineable_type === "\\App\\EstimateLineCategory" && element.parent !== null && element.parent.lineable_type === "\\App\\EstimateLineCategory"
        },
        is_separator: function (element) {
            return element.lineable_type === "\\App\\EstimateLineSeparator"
        },
        is_data_or_subdata: function (element) {
            return element.lineable_type === "\\App\\EstimateLineData"
        },
        is_data: function (element) {
            return element.lineable_type === "\\App\\EstimateLineData" && element.parent !== null && element.parent.lineable_type !== "\\App\\EstimateLineData"
        },
        is_subdata: function (element) {
            return element.lineable_type === "\\App\\EstimateLineData" && element.parent !== null && element.parent.lineable_type === "\\App\\EstimateLineData"
        },
        is_ficha: function (element) {
            return element.lineable_type === "\\App\\EstimateLineFicha"
        },
        //
        round10: function (num){
            return Math.round(num * 100) / 100;
        },
        workers_in_lines_spends(){
            var sum = 0;
            if (this.lines && this.lines.length > 0){
                sum = this.workers_in_lines_spends_recursive(this.lines);
            }
            return sum;
        },
        workers_in_lines_spends_recursive(lines_r){
            var sum = 0;
            lines_r.forEach(l => {

                l.workers.forEach(w =>{
                    sum += (w.salary || 0) * w.hours;
                });
                
                if (l.children && l.children.length > 0){
                    sum += this.workers_in_lines_spends_recursive(l.children);
                }                
            });

            return parseFloat(sum).toFixed(2);
        }
    },
    computed: {
        ...mapGetters({
            units: 'getEstimateUnits',
            unitsById: 'getEstimateUnitsById',
            users: 'getNotRemovedUsers',
            usersById: 'getUsersById',
            groupsById: 'getGroupsById',
        }),

    },
    watch: {
        lines: {
            deep: true,
            handler(){
                var value_from_lines = this.workers_in_lines_spends();
                if (value_from_lines > 0){
                    this.total_maodeobra = value_from_lines;
                }
                else{
                    this.total_maodeobra = this.total_maodeobra_general;
                }
            }            
        }
    }
}
</script>