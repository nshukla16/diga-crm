<style>
    .e-treegrid .e-treegridexpand,
    .e-treegrid .e-treegridcollapse {
        color: #000;
        cursor: pointer;
        font-size: 10px;
        height: 16px;
        text-align: center;
        vertical-align: bottom;
        width: 16px;
    }
    .e-treegrid .e-treegridexpand {
        transform: rotate(90deg);
        display: -webkit-inline-box;
        padding-left: 7px;
    }
    .e-treegrid .e-treegridexpand::before,
    .e-treegrid .e-treegridcollapse::before {
        content: "";
    }

    .e-treegrid .e-toolbar-item .e-expand::before {
        content: "";
    }

    .e-treegrid .e-toolbar-item .e-collapse::before {
        content: "";
    }

    .e-bigger .e-treegrid .e-treegridexpand,
    .e-bigger .e-treegrid .e-treegridcollapse {
        height: 18px;
        width: 18px;
    }

    .e-bigger .e-treegrid .e-rowcell.e-treerowcell {
        padding-left: 25px;
    }

    .e-bigger .e-treegrid .e-hierarchycheckbox {
        padding-left: 2px;
    }

    .e-treegrid .e-treegridexpand::before,
    .e-treegrid .e-treegridcollapse::before {
        vertical-align: middle;
    }
    #e-item_2{
        display: none;
    }
    .date-box-2{
        display: grid;
        grid-template-columns: 48% 48%;
        grid-column-gap: 2rem;
        margin-bottom: 1rem;
    }
    .work-hrs-box{
        display: grid;
        grid-template-columns: 46.5% 46.5%;
        grid-column-gap: 2rem;
        margin-bottom: 1rem;
    }
    .display-time{
        width: 100% !important;
        display: inline-block;
        height: 34px;
        padding: 6px 30px;
        padding-left: 10px;
        font-size: 14px;
        line-height: 1.4;
        color: #555;
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
        box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
    }
    .time-picker{
        width: 100% !important;

    }
    .time-picker .dropdown ul li.active, .time-picker .dropdown ul li.active:hover{
        background: #084756 !important;
    }
    .non-working-hours{
        background-color: green !important;
        color: greenyellow !important;
    }
    /* .e-gantt-tooltip{
        display: none !important;
    } */
    .notifications{
        z-index: 999;
    }
    .el-date-editor.el-input, .el-date-editor.el-input__inner{
        width: 100%;
    }
    .oder-arrow{
        color: #1b1e21;
        font-size: 1.7rem;
        vertical-align: middle;
    }
    /* To hide not used DIV from DOM of gantt component */
    .e-dlg-container{
        display: none !important;
    }
    .e-tooltip-wrap{
        background-color: rgba(97, 97, 97, 0.87);
        color: white;
    }
</style>

<template lang="pug">
    div
        div
            h2.d-inline-block {{ !is_custom ? $t('gantt.Estimate_planning') :  $t('gantt.Estimate_planning_custom') }}: {{ planning_name }}
            div.ml-3(style="display: inline-block" v-if="edit_button_to_show && arrow_up")
                i.change-order.fa.fa-sort-up.oder-arrow(@click="go_up")
            div.ml-3(style="display: inline-block" v-if="edit_button_to_show && arrow_down")
                i.change-order.fa.fa-sort-desc.oder-arrow(style="vertical-align: super" @click="go_down")
            select.form-control.float-right(style="width: 200px" v-model="scale")
                option(:value="0") {{ $t('gantt.Scale') }}
                option(:value="1") {{ $t('gantt.Day') }}
                option(:value="2") {{ $t('gantt.Week') }}
                option(:value="3") {{ $t('gantt.Month') }}
                option(:value="4") {{ $t('gantt.Year') }}
            button.float-sm-right.mr-2.ml-2.btn.btn-diga(@click='excel_export') Excel (.xlsx)
            button.float-sm-right.mr-2.ml-2.btn.btn-diga(@click='add_milestone') {{ $t('gantt.Add_milestone') }}
            button.float-sm-right.ml-2.btn.btn-diga(@click='open_working_hours') {{ $t('gantt.Working_hours') }}
            button.float-sm-right.ml-2.btn.btn-diga(@click='create_new_task_modal') {{ $t('gantt.Add_task') }}
            button.float-sm-right.ml-2.btn.btn-diga(v-if="edit_button_to_show" @click='open_task_modal') {{ $t('gantt.Edit_task') }}

        ejs-gantt(
            ref='gantt'
            id="GanttContainer"
            :dataSource= "data"
            :highlightWeekends= 'true'
            :taskFields= "taskFields"
            :labelSettings= "labelSettings"
            :includeWeekend="includeWeekend"
            :editSettings= "editSettings"
            :treeColumnIndex='1'
            :gridLines = "gridLines"
            :splitterSettings = "splitterSettings"
            :columns = "columns"
            :timelineSettings = "timelineSettings"
            :tooltipSettings = "tooltipSettings"
            :eventMarkers = "eventMarkers"
            :dayWorkingTime="dayWorkingTime"
            :rowDeselected="rowDeselected"
            :rowSelected="rowSelected"
            :locale="$root.locale"
            :selectedRowIndex="selectedRowIndex"
            @actionComplete="requests"
        )
        #milestone-modal.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-dialog-centered(role="document")
                .modal-content
                    .modal-header
                        h2.modal-title#myModalLabel {{ edit_add ? $t('gantt.Add_milestone') : $t('gantt.Edit_milestone') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body
                        fieldset.form-group(:class="{ 'has-error': errors.has('event.label') }")
                            label {{ $t('gantt.Milestone_name') }}
                            input.form-control.mr-2(v-model="event.label" name="event.label", v-validate="'required'", v-bind:data-vv-as="$t('gantt.Milestone_name').toLowerCase()")
                            h6.help-block(v-show="errors.has('event.label')") {{ errors.first('event.label') }}
                        fieldset.form-group
                            div.work-hrs-box
                                label {{ $t('gantt.Milestone_date') }}
                                p {{ $t('gantt.Start_time') }}
                                date-picker(format="MM/DD/YYYY" v-model="event.day", :lang="$root.locale", :width="'100%'")
                                TimePicker( placeholder="" v-model="event.time" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                        div(v-if="edit_add")
                            button.btn.btn-diga(@click='push_milestone') {{ $t('gantt.Add') }}
                        div(v-else)
                            button.btn.btn-diga.mr-2(@click='edit_milestone') {{ $t('gantt.Edit') }}
                            button.btn.btn-danger(@click='delete_milestone') {{ $t('gantt.Delete') }}
        #work_modal.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-dialog-centered(role="document")
                .modal-content
                    .modal-header
                        h2.modal-title#myModalLabel {{$t('gantt.Edit_working_hours')}}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body
                        div.work-hrs-box
                            fieldset.form-group
                                label {{ $t('gantt.Start_time') }}
                                input.form-control.mr-2(type="number" v-model="dayWorkingTime[0].from")
                            fieldset.form-group
                                label {{ $t('gantt.End_time') }}
                                input.form-control.mr-2(type="number" v-model="dayWorkingTime[0].to")
                        button.btn.btn-diga(@click='update_working_hours') {{ $t('gantt.Edit') }}
        #task_modal.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-lg.modal-dialog-centered(role="document")
                .modal-content
                    .modal-header
                        h2.modal-title#myModalLabel {{ $t('gantt.Edit_task') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body(v-if="currentTask !== null")
                        h4 {{ $t('project.Name') }}: {{ currentTask.TaskName }}
                        dir.row
                            div.col
                                div(v-if="!selected_is_parent")
                                    div.row
                                        div.col-6
                                            p(style="margin-top: 7px") {{ $t('gantt.Start_date') }}
                                            date-picker(format="YYYY-MM-DD" v-model="currentTask.StartDate", :lang="$root.locale", :width="'100%'")
                                        div.col-6
                                            p(style="margin-top: 7px") {{ $t('gantt.End_date') }}
                                            date-picker(format="YYYY-MM-DD" v-model="currentTask.EndDate", :lang="$root.locale", :width="'100%'")
                                    div.row
                                        div.col-6
                                            p(style="margin-top: 7px") {{ $t('gantt.Start_time') }}
                                            TimePicker( placeholder="" v-model="currentTask.StartTime" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                        div.col-6
                                            p(style="margin-top: 7px") {{ $t('gantt.End_time') }}
                                            TimePicker( placeholder="" v-model="currentTask.EndTime" :picker-options="{ start: '00:00', step: '00:15',end: '24:00'}")
                                fieldset.form-group(v-if="!selected_is_parent" :class="{ 'has-error': errors.has('task_name') }")
                                    label {{ $t('gantt.Task_progress') }}
                                    input.form-control.mr-2(v-model="currentTask.Progress" type="number" min="0" max="100")
                                <!--div(v-if="is_custom")-->
                                fieldset.form-group
                                    label {{ $t('gantt.Task_name') }}
                                    input.form-control.mr-2(v-model="currentTask.TaskName")
                                fieldset.form-group
                                    label {{ $t('gantt.Child_of') }}
                                    select.form-control(v-model="currentTask.parent_id")
                                        option(value='') {{ $t('gantt.No_parent') }}
                                        option(v-for="parent in parents_we_can_add", :value="parent.id") {{ parent.name }}
                                fieldset.form-group
                                    label {{ $t('gantt.Task_description') }}
                                    textarea.form-control.mr-2(v-model="currentTask.Note")
                            div.col
                                p(style="margin-top: 7px") {{ $t('gantt.Relations') }}
                                table.table.table-striped.w-100
                                    thead
                                        tr
                                            th ID
                                            th {{ $t('project.Name') }}
                                            th {{ $t('gantt.Relation_type') }}
                                            th {{ $t('gantt.Delete') }}
                                    tbody
                                        tr(v-for="dependency in tmp_dependencies")
                                            td(style="padding-right: 15px") {{ dependency[0].RowID}}
                                            td(style="padding-right: 15px") {{ dependency[0].TaskName}}
                                            td(style="padding-right: 15px") {{ dependency[1] }}
                                            td
                                                i.fa.fa-trash-o.clickable(@click="delete_dependency(dependency[0].TaskID)")
                        button.btn.btn-secondary.mr-2(@click='change_task') {{ $t('gantt.Change_task') }}
                        button.btn.btn-danger(@click='delete_custom_task') {{ $t('gantt.Delete') }}
        #add-new-task-modal.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-lg.modal-dialog-centered(role="document")
                .modal-content
                    .modal-header
                        h2.modal-title#myModalLabel {{ $t('gantt.New_task') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body(v-if="currentTask !== null")
                        div.date-box-2
                            p(style="margin-top: 7px") {{ $t('gantt.Start_date') }}
                            p(style="margin-top: 7px") {{ $t('gantt.End_date') }}
                            date-picker(format="YYYY-MM-DD" v-model="currentTask.StartDate", :lang="$root.locale", :width="'100%'")
                            date-picker(format="YYYY-MM-DD" v-model="currentTask.EndDate", :lang="$root.locale", :width="'100%'")
                            p(style="margin-top: 7px") {{ $t('gantt.Start_time') }}
                            p(style="margin-top: 7px") {{ $t('gantt.End_time') }}
                            TimePicker(v-model="currentTask.StartTime" :picker-options="{ start: '00:00', step: '00:15',end: '24:00'}")
                            TimePicker(v-model="currentTask.EndTime" :picker-options="{ start: '00:00', step: '00:15',end: '24:00'}")
                        fieldset.form-group(:class="{ 'has-error': errors.has('currentTask.TaskName') }")
                            label {{ $t('gantt.Task_name') }}
                            input.form-control.mr-2(v-model="currentTask.TaskName" name="currentTask.TaskName", v-validate="'required'", v-bind:data-vv-as="$t('gantt.Task_name').toLowerCase()")
                            h6.help-block(v-show="errors.has('currentTask.TaskName')") {{ errors.first('currentTask.TaskName') }}
                        fieldset.form-group(:class="{ 'has-error': errors.has('task_name') }")
                            label {{ $t('gantt.Task_progress') }}
                            input.form-control.mr-2(v-model="currentTask.Progress" type="number" min="0" max="100")
                        fieldset.form-group
                            label {{ $t('gantt.Child_of') }}
                            select.form-control(v-model="currentTask.parent_id")
                                option(value = '') {{ $t('gantt.No_parent') }}
                                option(v-for="parent in parents_we_can_add", :value="parent.id") {{ parent.name }}
                        fieldset.form-group
                            label {{ $t('gantt.Task_description') }}
                            textarea.form-control.mr-2(v-model="currentTask.Note")
                        button.btn.btn-secondary(@click='save_custom_task') {{ $t('gantt.Save_task') }}
</template>

<script>
import { GanttComponent, Sort, Filter, Edit, Selection, Toolbar, DayMarkers, ExcelExport, PdfExport, PdfExportProperties  } from "@syncfusion/ej2-vue-gantt";
import { loadCldr } from "@syncfusion/ej2-base/src/internationalization";
import { PdfTrueTypeFont } from '@syncfusion/ej2-pdf-export';
import moment from 'moment';
import VueTimepicker from 'vue2-timepicker';
import TimePicker from 'element-ui/lib/time-select';
require("element-ui/lib/theme-chalk/index.css");
require("@syncfusion/ej2-vue-gantt/styles/material.css");
require("@syncfusion/ej2-base/styles/material.css");
require("@syncfusion/ej2-layouts/styles/material.css"); // cant be erased
require("@syncfusion/ej2-grids/styles/material.css");

export default {
    components: {
        'ejs-gantt': GanttComponent, VueTimepicker, TimePicker,
    },
    props: ['id'],
    data(){
        return {
            originalLines: [],
            transformedLines: [],
            data: [],
            tmp_split: null,
            selectedTask: false,
            eventMarkers: [],
            test: null,
            scale: 0,
            event: {
                id: '',
                day: '',
                time: '',
                label: '',
            },
            selectedMilestoneId: '',
            currentTask: null,
            tmp_dependencies: [],
            edit_add: true,
            check: 'true',
            planning_name: '',
            taskFields: {
                order_number: 'order_number',
                rowID: 'RowID',
                id: 'TaskID',
                name: 'TaskName',
                Note: 'Note',
                startDate: 'StartDate',
                endDate: 'EndDate',
                dependency: 'Predecessor',
                progress: 'Progress',
                parentID: 'ParentId',
                duration: 'Duration',
            },
            locale: 'pt',
            dayWorkingTime: [
                {
                    from: 0,
                    to: 23,
                },
            ],
            // toolbar: ['ExcelExport', 'CsvExport', 'PdfExport'],
            gridLines: 'Both',
            splitterSettings: {
                columnIndex: 4,
            },
            columns: [
                { field: 'TaskID', width: '80' },
                { field: 'TaskName', headerText: this.$root.$t('gantt.Task_name'), width: '500' },
                { field: 'StartDate', headerText: this.$root.$t('gantt.Start_date'), width: '150' },
                { field: 'EndDate', headerText: this.$root.$t('gantt.End_date'), width: '150' },
                // { field: 'Note', headerText: this.$root.$t('gantt.Task_description'), width: '250' },
                { field: 'Duration', headerText: this.$root.$t('gantt.Duration'), width: '90' },
                { field: 'Workers', headerText: this.$root.$t('hr.Users'), width: 300 },
                // { field: 'UnitMeasurement', headerText: this.$root.$t('estimate.Un'), width: 100 },
                // { field: 'Quantity', headerText: this.$root.$t('estimate.Quantity'), width: 100 },

                { field: 'Progress', headerText: this.$root.$t('gantt.Progress'), width: '90' },                
                { field: 'RowID', headerText: this.$root.$t('gantt.Row_id'), width: '0'},
                { field: 'order_number', headerText: 'orderID', width: '0'},
            ],
            labelSettings: {
                taskLabel: '${Progress}%',
            },
            includeWeekend: true,
            editSettings: {
                allowAdding: false,
                allowEditing: false, // this was true
                allowDeleting: false,
                allowTaskbarEditing: true, // this was true
                showDeleteConfirmDialog: false,
                mode: 'Auto',
            },
            tooltipSettings: {
                showTooltip: true
            },
            timelineSettings: {
                topTier: {
                    format: 'MMM dd, yyyy',
                },
                bottomTier: {
                    format: 'HH:mm',
                },
                timelineViewMode: 'Day',
                timelineUnitSize: 45,
            },
            selectedRowLineId: null,
            is_custom: null,
            p_id: null,
            temp_pool: [],
            edit_button_to_show: false,
            selected_is_parent: false,
            selectedRowIndex: null,
            selected_index: null,
        };
    },
    created(){
        this.load_plan();
        this.load_milestones();
        loadCldr(
            // RU lang
            require('../../../../../../../../node_modules/cldr-data/supplemental/numberingSystems.json'),
            require('../../../../../../../../node_modules/cldr-data/main/ru/ca-gregorian.json'),
            require('../../../../../../../../node_modules/cldr-data/main/ru/currencies.json'),
            require('../../../../../../../../node_modules/cldr-data/main/ru/numbers.json'),
            require('../../../../../../../../node_modules/cldr-data/main/ru/timeZoneNames.json'),
            // PT lang
            require('../../../../../../../../node_modules/cldr-data/main/pt/ca-gregorian.json'),
            require('../../../../../../../../node_modules/cldr-data/main/pt/currencies.json'),
            require('../../../../../../../../node_modules/cldr-data/main/pt/numbers.json'),
            require('../../../../../../../../node_modules/cldr-data/main/pt/timeZoneNames.json'),
        );
    },
    mounted(){
        this.attach_listeners();
        this.non_work_hrs();
        this.recalculate_timeline_height(window.innerHeight);
        this.$nextTick(() => {
            window.addEventListener('resize', () => {
                this.recalculate_timeline_height(window.innerHeight);
            });
        });
    },
    beforeDestroy(){
        this.deattach_listeners();
    },
    computed: {
        parents_we_can_add(){
            if(this.currentTask.RowID === ""){
                return this.temp_pool;
            } else {
                return this.temp_pool.filter(parent => parent.id !== this.selectedRowLineId);
            }
        },
        current_computed(){
            return this.temp_pool.filter(itm => itm.id === this.selectedRowLineId);
        },
        same_parent_list(){
            if(this.selectedRowLineId !== null){
                return this.temp_pool.filter(parent => parent.parent_id === this.current_computed[0].parent_id);
            }
        },
        child_list_of_parent(){
            if(this.currentTask.parent_id === ""){
                this.currentTask.parent_id = null;
            }
            return this.temp_pool.filter(child => child.parent_id == this.currentTask.parent_id);
        },
        arrow_up(){
            let index_of = this.same_parent_list.findIndex(index => index.id == this.current_computed[0].id);
            if(index_of !== 0 && this.same_parent_list[index_of - 1].order_number !== null && this.current_computed[0].order_number !== null){
                return true;
            }
            return false;
        },
        arrow_down(){
            let index_of = this.same_parent_list.findIndex(index => index.id == this.current_computed[0].id);
            if(index_of !== this.same_parent_list.length - 1 && this.same_parent_list[index_of + 1].order_number !== null && this.current_computed[0].order_number !== null){
                return true;
            }
            return false;
        }
    },
    watch: {
        current_computed(newVal, oldVal){
            if(oldVal.length !== 0){
                this.selected_index = oldVal[0].id;
            }
        },
        scale(newVal){
            switch (newVal){
            case 0:
                break;
            case 1:
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.unit = 'Day';
                this.$refs.gantt.ej2Instances.timelineSettings.timelineUnitSize = 45;
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.unit = 'Hour';
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.format = 'HH:mm';
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.format = 'MMM dd, yyyy';
                this.non_work_hrs();
                this.scale = 0;
                break;
            case 2:
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.unit = 'Week';
                this.$refs.gantt.ej2Instances.timelineSettings.timelineUnitSize = 75;
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.unit = 'Day';
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.format = 'MMM dd';
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.format = 'MMM dd, yyyy';
                this.scale = 0;
                break;
            case 3:
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.unit = 'Month';
                this.$refs.gantt.ej2Instances.timelineSettings.timelineUnitSize = 50;
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.unit = 'Day';
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.format = 'MMM dd';
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.format = 'MMM dd, yyyy';
                this.scale = 0;
                break;
            case 4:
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.unit = 'Year';
                this.$refs.gantt.ej2Instances.timelineSettings.timelineUnitSize = 200;
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.unit = 'Month';
                this.$refs.gantt.ej2Instances.timelineSettings.bottomTier.format = 'MMM ';
                this.$refs.gantt.ej2Instances.timelineSettings.topTier.format = 'yyyy';
                this.scale = 0;
                break;
            }
        },
    },
    methods: {
        excel_export(){
            this.$root.global_loading = true;
            this.$http.post('/api/estimate_plannings/' + this.id + '/export_excel', {
            }, {responseType:'arraybuffer'})
            .then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.forceFileDownload(res);
                }                

                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });            
        },
        forceFileDownload(response){
            const url = window.URL.createObjectURL(new Blob([response.data]))
            const link = document.createElement('a')
            link.href = url
            link.setAttribute('download', 'Gantt-chart.xlsx') //or any other extension
            document.body.appendChild(link)
            link.click()
        },
        toolbarClick:function (args) {         
            if (args.item.id === 'GanttContainer_excelexport') {
                this.$refs.gantt.ej2Instances.excelExport();
            }
            else if (args.item.id === 'GanttContainer_csvexport') {
                this.$refs.gantt.ej2Instances.csvExport();
            } 
            else if (args.item.id === 'GanttContainer_pdfexport') {
                var exportProperties = {
                    pageSize: 'A0',
                };
                this.$refs.gantt.ej2Instances.pdfExport(exportProperties);
            } 
        },
        getBase64(url) {
            this.$http.get(url)
                .then(res => {
                    return btoa(String.fromCharCode(...new Uint8Array(response.data)));
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
        },
        open_working_hours() {
            $('#work_modal')
                .modal('show');
        },
        update_working_hours() {
            this.$root.global_loading = true;
            this.$http.post('/api/estimate_plannings/' + this.id + '/update_work_hours', {
                start_time: this.dayWorkingTime[0].from,
                end_time: this.dayWorkingTime[0].to,
            })
                .then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        $('#work_modal')
                            .modal('hide');
                        this.$refs.gantt.ej2Instances.refresh();
                        // This func colors non working hours.
                        this.non_work_hrs();
                        this.$toastr.s(this.$root.$t("client.Settings_saved"), this.$root.$t("template.Success"));
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
        },
        rowDeselected() {
            this.selectedRowLineId = null;
            this.edit_button_to_show = false;
            this.selected_is_parent = false;
        },
        rowSelected(args) {
            this.selectedRowLineId = args.data.TaskID;
            if(args.data.hasChildRecords === true){
                this.selected_is_parent = true;
            }
            this.edit_button_to_show = true;
        },
        recalculate_timeline_height(newHeight) {
            this.$refs.gantt.ej2Instances.height = newHeight - 220;
            this.$refs.gantt.ej2Instances.refresh();
            this.non_work_hrs();
        },
        requests(data) {
            this.non_work_hrs();
            if (data.requestType === 'openEditDialog') {
                data.cancel = true;
            } else if (data.requestType === 'save') {
                this.change_task_on_move(data);
            } else if (data.requestType === 'refresh'){
                    // console.log(this.selected_index);
                    // console.log(this.$refs.gantt.ej2Instances.currentViewData);

                    this.$refs.gantt.ej2Instances.selectedRowIndex = this.$refs.gantt.ej2Instances.currentViewData.findIndex(index => index.TaskID === this.selected_index);
                    // console.log(this.$refs.gantt.ej2Instances.currentViewData.findIndex(index => index.TaskID === this.selected_index));
                    // console.log(this.$refs.gantt.ej2Instances.selectedRowIndex);
                    this.$refs.gantt.ej2Instances.refresh();
            }

        },
        transformLines(array) {
            return array.map(e => {
                return {
                    ...e,
                    RowID: !this.is_custom ? e.line_number : e.id,
                    TaskID: e.id,
                    ParentId: e.parent_id,
                    StartDate: moment(e.start_datetime).format('YYYY-MM-DD HH:mm'),
                    EndDate: moment(e.end_datetime).format('YYYY-MM-DD HH:mm'),
                    StartTime: moment(e.start_datetime).format('HH:mm'),
                    EndTime: moment(e.end_datetime).format('HH:mm'),
                    TaskName: e.name,
                    Progress: e.progress,
                    Predecessor: e.predecessor,
                    Note: e.description,
                    parent_id: e.parent_id,
                    order_number: e.order_number,
                    Workers: e.estimate_line === null ? '' : e.estimate_line.estimate_line_workers.map(o => o.user.name).join(', '),
                    // UnitMeasurement: e.estimate_line.data_measure,
                    // Quantity: e.estimate_line.data_quantity,
                }
            });
        },
        load_plan() {
            this.$root.global_loading = true;
            this.$http.get('/api/estimate_plannings/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.is_custom = res.data.is_custom;
                    this.originalLines = res.data.estimate_planning_lines;

                    this.transformedLines = this.transformLines(this.originalLines);
                    this.data = this.transformedLines;
                    this.temp_pool = [];
                    for (let i = 0; i < this.originalLines.length; i++){
                        this.temp_pool.push(this.originalLines[i]);
                    }
                    this.planning_name = res.data.name;
                    this.dayWorkingTime[0].from = res.data.start_time;
                    this.dayWorkingTime[0].to = res.data.end_time;
                    if (!this.selectedTask){
                        this.edit_button_to_show = false;
                    }
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        load_milestones() {
            this.$root.global_loading = true;
            this.$http.get('/api/estimate_plannings/' + this.id + '/show_milestone')
                .then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        let data = res.data;
                        this.eventMarkers = data.map(ms => {
                            return {
                                id: ms.id,
                                day: ms.datetime,
                                label: ms.name
                            }
                        });
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
        },
        gen_dependencies() {
            let temp_split = this.currentTask.Predecessor.split(',');
            let ids = [];
            let dpdc = [];
            // Generates array only with ID's
            for (let i = 0; i < temp_split.length; i++) {
                if (temp_split[i]) {
                    let connection_type = '';
                    ['SS', 'FF', 'SF', 'FS'].forEach((e) => {
                        if (temp_split[i].indexOf(e) != -1) {
                            connection_type = e;
                        }
                    });
                    switch (connection_type) {
                    case 'SS':
                        connection_type = this.$root.$t("gantt.SS");
                        break;
                    case 'FF':
                        connection_type = this.$root.$t("gantt.FF");
                        break;
                    case 'SF':
                        connection_type = this.$root.$t("gantt.SF");
                        break;
                    case 'FS':
                        connection_type = this.$root.$t("gantt.FS");
                        break;
                    default:
                        connection_type = '--';
                        break;
                    }
                    ids.push([temp_split[i].match(/\d+/)[0], connection_type]);
                }
            }
            // Finds object by ID of a dependency. Later it will print a name and relation type in modal of dependencies
            for (let i = 0; i < ids.length; i++) {
                // leave filter with '=='!!! Otherwise it will cause a bug.
                let id_to_name = this.transformedLines.filter(e => e.id == ids[i][0]);
                dpdc.push([id_to_name[0], ids[i][1]]);
            }
            this.tmp_dependencies = dpdc;
            this.temp_split = this.currentTask.Predecessor.split(',');
        },
        delete_dependency(id) {
            this.tmp_dependencies = this.tmp_dependencies.filter((e) => {
                return e[0].TaskID !== id;
            });
            this.temp_split = this.temp_split.filter(function (el) {
                return el.indexOf(id) === -1;
            });
            let array_to_string = this.temp_split.toString();
            this.currentTask.Predecessor = array_to_string;
        },
        non_work_hrs() {
            setTimeout(() => {
                for (let i = 0; i < 24; i++) {
                    let element = '0' + i + ':00';
                    if (i >= 10) {
                        element = i + ':00';
                    }
                    if (i < this.dayWorkingTime[0].from || i >= this.dayWorkingTime[0].to) {
                        $("th:contains('" + element + "')").css("background-color", "#ededed");
                    }
                }
            }, 30);
        },
        attach_listeners() {
            let $this = this;
            $(document)
                .on('click', '.e-span-label', function (e) {
                    if ($this.open_milestone($(this)[0].innerText)) {
                        e.preventDefault();
                    }
                });
        },
        deattach_listeners(){
            $(document).off('click', '.e-span-label');
        },
        open_task_modal() {
            this.currentTask = JSON.parse(JSON.stringify(this.findById(this.data, this.selectedRowLineId)));
            this.gen_dependencies();
            if (!this.selected_is_parent){
                this.currentTask.EndTime = moment(this.currentTask.EndDate).format("HH:mm");
                this.currentTask.StartTime = moment(this.currentTask.StartDate).format("HH:mm");
             }
            $('#task_modal').modal('show');
        },
        change_task_on_move(res) {
            let data = res.modifiedRecords;
            for (let i = 0; i < data.length; i++) {
                // this.$root.global_loading = true;
                this.$http.post('/api/estimate_plannings/' + data[i].TaskID + '/update_task', {
                    parent_id: data[i].parentItem ? data[i].parentItem.taskId : '',
                    StartDate: moment(data[i].StartDate).format('YYYY-MM-DD HH:mm'),
                    EndDate: moment(data[i].EndDate).format('YYYY-MM-DD HH:mm'),
                    Progress: data[i].Progress,
                    Predecessor: data[i].Predecessor,
                    Note: data[i].Note,
                    name: data[i].TaskName,
                    order_number: data[i].order_number,
                })
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            let el = this.data.find(e => e.TaskID === data[i].TaskID);
                            el.Predecessor = data[i].Predecessor;
                            // Usually makes more than one request, so better not to add "success" messages here

                            // let data = this.data.filter(e => e.TaskID === data[i].TaskID);
                                el.ParentId = data[i].parentItem ? data[i].parentItem.taskId : '';
                                el.StartDate = moment(data[i].StartDate).format('YYYY-MM-DD HH:mm');
                                el.EndDate = moment(data[i].EndDate).format('YYYY-MM-DD HH:mm');
                                el.Progress = data[i].Progress;
                                el.Predecessor = data[i].Predecessor;
                                el.Note = data[i].Note;
                                el.name = data[i].TaskName;
                                el.order_number = data[i].order_number;
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
            }
        },
        change_task() {
            if(this.child_list_of_parent.length === 0){
                this.currentTask.order_number = 1;
            } else if(this.child_list_of_parent[this.child_list_of_parent.length - 1].id !== this.currentTask.id){
                this.currentTask.order_number = this.child_list_of_parent[this.child_list_of_parent.length - 1].order_number + 1;
            }
            let oldTask = this.findById(this.data, this.selectedRowLineId);
            let start = moment();
            let end = moment();
            if (!this.selected_is_parent){
                [start, end] = this.calculate_date();
            }
            this.$root.global_loading = true;
            this.$http.post('/api/estimate_plannings/' + this.currentTask.id + '/update_task', {
                name: this.currentTask.TaskName,
                TaskName: this.currentTask.TaskName,
                StartDate: moment(start).format('YYYY-MM-DD HH:mm:00'),
                EndDate: moment(end).format('YYYY-MM-DD HH:mm:00'),
                parent_id: this.currentTask.parent_id,
                Predecessor: this.currentTask.Predecessor,
                Progress: this.currentTask.Progress,
                Note: this.currentTask.Note,
                order_number: this.currentTask.order_number,
            })
                .then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        Object.assign(oldTask, this.currentTask);

                        this.load_plan();
                        this.non_work_hrs();
                        $('#task_modal').modal('hide');
                        this.$toastr.s(this.$root.$t("client.Settings_saved"), this.$root.$t("template.Success"));
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
        },
        findById(obj, id) {
            var result;
            for (var p in obj) {
                if (obj.id === id) {
                    return obj;
                } else {
                    if (typeof obj[p] === 'object') {
                        result = this.findById(obj[p], id);
                        if (result) {
                            return result;
                        }
                    }
                }
            }
            return result;
        },
        open_milestone(name) {
            this.edit_add = false;
            let milestone = this.eventMarkers.find(g => g.label == name);
            this.selectedMilestoneId = milestone.id;
            this.event.label = milestone.label;
            this.event.id = milestone.id;
            this.event.day = milestone.day;
            this.event.time = moment(milestone.day).format('HH:mm');
            $('#milestone-modal').modal('show');
        },
        add_milestone() {
            this.edit_add = true;
            this.event.day = moment().format('YYYY-MM-DD');
            this.event.label = '';
            this.event.time = moment().format('HH:mm');
            $('#milestone-modal').modal('show');
        },
        edit_milestone() {
            let temp = 0;
            for (let i = 0; i < this.eventMarkers.length; i++) {
                if (this.eventMarkers[i].id === this.selectedMilestoneId) {
                    temp = i;
                }
            }
            let startDate = moment(this.event.day).format('YYYY-MM-DD') + ' ' + this.event.time;
            this.$http.post('/api/estimate_plannings/' + this.selectedMilestoneId + '/update_milestone', {
                day: moment(startDate).format('YYYY-MM-DD HH:mm:00'),
                name: this.event.label,
            })
                .then(res => {
                    this.$refs.gantt.ej2Instances.eventMarkers[temp].label = this.event.label;
                    this.$refs.gantt.ej2Instances.eventMarkers[temp].day = startDate;
                    this.eventMarkers[temp].day = startDate;
                    this.eventMarkers[temp].label = this.event.label;
                    this.non_work_hrs();
                    $('#milestone-modal').modal('hide');
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
        },
        push_milestone() {
            this.$validator.validate("event.label")
                .then(result => {
                    if (!result) {
                        this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                        return;
                    }
                    let startDate = moment(this.event.day).format('YYYY-MM-DD') + ' ' + this.event.time;
                    this.$root.global_loading = true;
                    this.$http.post('/api/estimate_plannings/store_milestone', {
                        day: startDate,
                        name: this.event.label,
                        estimate_planning_id: this.id,
                    })
                        .then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            } else {
                                this.eventMarkers.push({
                                    id: res.data.id,
                                    day: startDate,
                                    label: this.event.label,
                                });
                                this.$refs.gantt.ej2Instances.refresh();
                                this.non_work_hrs();
                                $('#milestone-modal').modal('hide');
                            }
                            this.$root.global_loading = false;
                        }, res => {
                            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                        });
                });
        },
        delete_milestone() {
            if (confirm(this.$root.$t('gantt.Are_you_sure_you_want_to_delete_milestone'))) {
                this.$http.delete('/api/estimate_plannings/' + this.event.id + '/destroy_milestone')
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.eventMarkers = this.eventMarkers.filter((e) => {
                                return e.id !== this.event.id;
                            });
                            $('#milestone-modal').modal('hide');
                            this.$refs.gantt.ej2Instances.refresh();
                            this.non_work_hrs();
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    });
            }
        },
        close_modal() {
            $('#milestone-modal').modal('hide');
        },
        create_new_task_modal() {
            this.currentTask = {
                TaskName: '',
                RowID: '',
                order_number: '',
                StartDate: moment(),
                EndDate:  moment().add(1, 'days'),
                Progress: 0,
                Predecessor: '',
                Note: '',
                parent_id: null,
                ParentId: '',
                StartTime: moment().format("HH:mm"),
                EndTime: moment().format("HH:mm"),
            };
            $('#add-new-task-modal').modal('show');
        },
        calculate_date() {
            var startDate = moment(this.currentTask.StartDate).format('YYYY-MM-DD') + ' ' + this.currentTask.StartTime;
            var endDate = moment(this.currentTask.EndDate).format('YYYY-MM-DD') + ' ' + this.currentTask.EndTime;
            let s = moment(startDate).format('YYYY-MM-DD HH:mm:00');
            let e = moment(endDate).format('YYYY-MM-DD HH:mm:00');
            return [s, e];
        },
        save_custom_task() {
            if(this.child_list_of_parent.length === 0){
                this.currentTask.order_number = 1;
            } else {
                this.currentTask.order_number = this.child_list_of_parent[this.child_list_of_parent.length - 1].order_number + 1;
            }

            this.$validator.validate('currentTask.TaskName').then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let [start, end] = this.calculate_date();
                this.$root.global_loading = true;
                this.$http.post('/api/estimate_planning_lines/', {
                    estimate_planning_id: this.id,
                    start_datetime: start,
                    end_datetime: end,
                    progress: this.currentTask.Progress,
                    description: this.currentTask.Note,
                    predecessor: this.currentTask.Predecessor,
                    name: this.currentTask.TaskName,
                    parent_id: this.currentTask.parent_id,
                    order_number: this.currentTask.order_number
                })
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.load_plan();
                            this.$toastr.s(this.$root.$t("client.Settings_saved"), this.$root.$t("template.Success"));
                            $('#add-new-task-modal').modal('hide');
                        }
                        this.$root.global_loading = false;
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                        this.$root.global_loading = false;
                    });
            });
        },
        delete_custom_task(){
            if (confirm(this.$root.$t('gantt.Are_you_sure_you_want_to_delete_milestone'))) {
                this.$http.delete('/api/estimate_planning_lines/' + this.selectedRowLineId)
                    .then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            this.dependencies_checking(this.selectedRowLineId);

                            this.non_work_hrs();
                            $('#task_modal').modal('hide');
                            this.$toastr.s(this.$root.$t("client.Settings_saved"), this.$root.$t("template.Success"));
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    });
            }
        },
        go_up(){
            let index_of_task = this.same_parent_list.findIndex(index => index.id == this.current_computed[0].id);
            if(index_of_task !== 0 && this.same_parent_list[index_of_task - 1].order_number !== null && this.current_computed[0].order_number !== null){
                let temp = this.same_parent_list[index_of_task - 1].order_number;
                this.same_parent_list[index_of_task - 1].order_number = this.current_computed[0].order_number;
                this.current_computed[0].order_number = temp;
                let arr = [];
                arr.push(this.current_computed[0],this.same_parent_list[index_of_task - 1]);
                this.shift_task(arr);
            }
        },
        go_down(){
            let index_of_task = this.same_parent_list.findIndex(index => index.id == this.current_computed[0].id);
            if(index_of_task !== this.same_parent_list.length - 1 && this.same_parent_list[index_of_task + 1].order_number !== null && this.current_computed[0].order_number !== null){
                let temp = this.same_parent_list[index_of_task + 1].order_number;
                this.same_parent_list[index_of_task + 1].order_number = this.current_computed[0].order_number;
                this.current_computed[0].order_number = temp;
                let arr = [];
                arr.push(this.current_computed[0],this.same_parent_list[index_of_task + 1]);
                this.shift_task(arr);
            }
        },
        shift_task(arr) {
            for (let i = 0; i < arr.length; i++) {
                this.$http.post('/api/estimate_plannings/' + arr[i].id + '/update_task', {
                    name: arr[i].name,
                    TaskName: arr[i].name,
                    StartDate: arr[i].start_datetime,
                    EndDate: arr[i].end_datetime,
                    parent_id: arr[i].parent_id,
                    Predecessor: arr[i].predecessor,
                    Progress: arr[i].Progress,
                    Note: arr[i].Note,
                    order_number: arr[i].order_number,
                }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.load_plan();
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                });
            }
        },
        dependencies_checking(deleted_id){
            let temp_id;
            let temp_arr_pred = [];
            let str_after_check = '';
            for (let i = 0; i < this.data.length; i++){
                if (this.data[i].Predecessor !== ''){
                    // Gets a temporary array of dependencies of each element
                    temp_id = this.data[i].TaskID;
                    let temp_split = this.data[i].Predecessor.split(',');
                    // Checks each dependency for a mach
                    for (let j = 0; j < temp_split.length; j++){
                        if (temp_split[j].match(/\d+/)[0] == deleted_id){
                            temp_split[j] = '';
                        }
                        temp_arr_pred.push(temp_split[j]);
                    }
                    str_after_check = temp_arr_pred.filter(Boolean).join(", ");
                    this.data[i].Predecessor = str_after_check;

                    this.$http.post('/api/estimate_plannings/' + this.data[i].TaskID + '/update_task', {
                        Predecessor: this.data[i].Predecessor,
                        StartDate: this.data[i].StartDate,
                        EndDate: this.data[i].EndDate,
                        Progress: this.data[i].Progress,
                        description: this.data[i].Note,
                        name: this.data[i].TaskName,
                        parent_id: this.data[i].parent_id,
                        Note: this.data[i].Note,
                    })
                        .then(res => {
                            if (res.data.errcode == 1) {
                                this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                            } else {
                                //
                            }
                            this.$root.global_loading = false;
                        }, res => {
                            this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                            this.$root.global_loading = false;
                        });
                }
            }
            this.load_plan();
        },
    },
    provide: {
        gantt: [Sort, Filter, Edit, Selection, Toolbar, DayMarkers, ExcelExport, PdfExport],
    },
}
</script>