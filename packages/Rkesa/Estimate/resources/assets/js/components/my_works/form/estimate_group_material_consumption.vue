<style>
</style>

<template lang="pug">
    div.diga-container(style="height: 750px; width: 100%")
        div.portlet-title
            div.caption
                span.caption-subject.bold.uppercase.ml-2 {{$t('template.Material_consumption')}}
        div.portlet-body
            div.vb.vb-invisible(style="height: 730px; position: relative; overflow: hidden;")
                div.vb-content(style="display: block; overflow: hidden scroll; height: 100%; width: calc(100% + 26px);")
                    div.text-center
                        fieldset.form-group(style="margin-top: 10px;")
                            label(style="margin-right: 10px;") {{ $t('project.Date') }}
                            date-picker(format="YYYY-MM-DD" v-model="date", :lang="$root.locale", :width="'30%'")
                            button.btn.btn-diga(:disabled="$root.user.can_enter_timesheet_and_consumption !== true" v-on:click="save_day()" style="margin-left: 10px;") {{$t('template.save_day')}}
                        div.table-responsive
                            table.table
                                thead
                                    tr.d-flex
                                        th.col-3 {{$t('calendar.Service')}}
                                        th.col-3 {{$t('estimate.Material')}}

                                        th.col-2 {{$t('estimate.Unidades')}}
                                        th.col-2 {{$t('dashboard.quantity')}}
                                        th.col-2
                                tbody
                                    tr.d-flex(v-for="current_material in current_materials")
                                        td.col-3
                                            select(class="form-control", v-model="current_material.estimate_line_category_id")
                                                option(v-for="c in estimate_line_categories", :value="c.id") {{ c.name }}
                                        td.col-3
                                            select(class="form-control", v-model="current_material.resource_id" @change="onResourceSelect($event, current_material)")
                                                option(v-for="c in resources", :value="c.id") {{ c.name }}
                                        td.col-2
                                            select(class="form-control", v-model="current_material.estimate_unit_id" disabled="disabled")
                                                option(v-for="unit in units", :value="unit.id") {{ unit.measure }}
                                        td.col-2
                                            input.form-control(type="number" v-model="current_material.quantity")
                                        td.col-2
                                            button.btn.btn-danger(v-on:click="remove(current_material)" style="margin-left: 10px;") {{$t('estimate.Delete')}}
                                    tr
                                        button.btn.btn-diga(v-on:click="add_material()" style="margin-left: 10px;") {{$t('client.Add')}}

</template>

<script>
import moment from 'moment';
import {mapGetters} from "vuex";
import TimePicker from 'element-ui/lib/time-select';
import VueTimepicker from 'vue2-timepicker';

require("element-ui/lib/theme-chalk/index.css");
require("@syncfusion/ej2-vue-gantt/styles/material.css");
require("@syncfusion/ej2-base/styles/material.css");
require("@syncfusion/ej2-layouts/styles/material.css"); // cant be erased
require("@syncfusion/ej2-grids/styles/material.css");

export default {
    props: ['estimate', 'group_id'],
    components: { TimePicker, VueTimepicker },
    data() {
        return {
            date: moment().startOf('day'),
            current_materials: [],
            estimate_line_categories: [],
            resources: [],
        }
    },
    mounted(){
        this.getEstimateLineCategories();
        this.getEstimateResources();
        this.getResults();        
    },
    methods: {
        getResults() {
            this.$root.global_loading = true;

            this.$http.get('/api/estimate_group_material_consumption?' + 'estimate_id=' + this.estimate.id + '&group_id=' + this.group_id + '&date=' + encodeURIComponent(moment(this.date).format('YYYY-MM-DD'))).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.current_materials = data.rows;

                    if (this.current_materials.length === 0){
                        this.current_materials.push({
                            id: 0,
                            date: moment(this.date),
                            estimate_line_category_id: 0,
                            resource_id: 0,
                            estimate_unit_id: this.units[0].id,
                            quantity: 0
                        });
                    }
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        getEstimateLineCategories(){
            this.$http.get('/api/estimate_line_categories?' + 'estimate_id=' + this.estimate.id ).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.estimate_line_categories = data.rows;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });           
        },

        getEstimateResources(){
            this.$http.get('/api/estimate_resources?' + 'estimate_id=' + this.estimate.id ).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.resources = data.rows;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });           
        },

        save_day(){
            this.$root.global_loading = true;

            var payload = JSON.parse(JSON.stringify(this.current_materials));
            payload.forEach(p => {
                p.date = moment(p.date).format('YYYY-MM-DD');           });

            this.$http.post('/api/estimate_group_material_consumption', 
            {
                group_id: this.group_id,
                estimate_id: this.estimate.id,
                estimate_group_material_consumption: payload
            }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error")); 
                } else {
                    this.$toastr.s(this.$root.$t('client.State_saved'), this.$root.$t("template.Success"));
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        onResourceSelect(event, mat){
            this.resources.forEach( r => {
                if (parseInt(r.id) === parseInt(event.target.value)){
                    mat.estimate_unit_id = r.estimate_unit_id;
                }
            });            
        },
        add_material(){
            this.current_materials.push({
                id: 0,
                date: moment(this.date),
                estimate_line_category_id: 0,
                resource_id: 0,
                estimate_unit_id: this.units[0].id,
                quantity: 0
            });
        },
        remove(material){
            if (confirm(this.$root.$t("estimate_fork.Are_you_sure_want_to_delete_this"))){
                let index = this.current_materials.indexOf(material);
                this.current_materials.splice(index, 1);
            }
        },
    },
    computed: {
        ...mapGetters({
            usersById: 'getUsersById',
            units: 'getEstimateUnits',
            units_by_id: 'getEstimateUnitsById',
            users: 'getNotRemovedUsers',
            users_by_id: 'getUsersById',
            groups: 'getGroups',
            groupsById: 'getGroupsById',
        }),
    },
    watch:{
        date: {
            handler(new_date){

                //if (confirm(this.$root.$t("template.do_you_want_to_save"))){
                    //this.save_day();
                //}

                this.getResults();
            }            
        }
    }
}
</script>