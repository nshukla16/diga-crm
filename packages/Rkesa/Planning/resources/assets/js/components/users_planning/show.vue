<style>
    .vis-line, .vis-dot{
        display: none;
    }
    .vis-item .vis-delete, .vis-item .vis-delete-rtl{
        height: 35px;
    }
    .picker-box{
     display: grid;
     grid-template-columns: 50% 50%;
    }
    .date-box{
        display: grid;
        grid-template-columns: 45% 45%;
        grid-column-gap: 2rem;
    }
    .dot{
        -webkit-border-radius: 20px;
        -moz-border-radius: 20px;
        border-radius: 20px;
        height: 20px;
        width: 20px;
        cursor: pointer;
        position: absolute;
        top: 50%;
        margin-top: -10px;
        right: 1%;
    }
    .form-group{
        position: relative;
    }
    .vis-timeline{
        background: #EEE;
    }
    .vis-item .vis-delete:after, .vis-item .vis-delete-rtl{
        font: normal normal normal 14px/1 FontAwesome;
    }
    .vis-item .vis-delete:after, .vis-item .vis-delete-rtl:before{
        content: "\F044";
        display: inline-block;
        font-size: 21px;
        padding-top: 7px;
        color: #4eaf15;
    }
    .vis-item .vis-delete, .vis-item .vis-delete-rtl{
        width: 29px;
    }
    .vis-delete:hover{
        background-color: #0faa30 !important;
        border-radius: 0 4px 4px 0;
    }
    .vis-delete{
        border-radius: 0 4px 4px 0;
    }
    .vis-item .vis-delete{
        right: -30px;
    }
    .vis-saturday, .vis-sunday{
        background-color: #d8d8d8;
        opacity: .3;
    }
    .vis-text.vis-saturday, .vis-text.vis-sunday{
        opacity: 1;
        color: #FFF;
    }
    .display-time{
        width: 100% !important;
        display: inline-block;
        width: 100%;
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
    .color-popup{
        position: absolute;
        z-index: 6;
        padding: 12px;
        right: 15px;
        margin: 39px;
    }
    .time-picker .dropdown ul li.active, .time-picker .dropdown ul li.active:hover{
        background: #084756 !important;
    }
    .el-date-editor.el-input, .el-date-editor.el-input__inner{
        width: 100%;
    }
     .mx-calendar-icon {
         margin-top: 4px;
         height: auto;
     }
    .content{
        display: inline-block;
        margin: 0;
        padding-right: .5rem;
    }
    .single-link-to-gantt{
        display: none;
        color: #4d4d4d;
        margin: -3px -14px -14px -14px;
        padding-left: 17px;
        padding-right: 17px;
    }
    .single-link-to-gantt:hover{
        background-color: #FEFEFE;
        text-decoration: none;
        color: #1d2124;
    }
    .gantt-links-box{
        display: none;
        position: absolute;
        padding: 5px 15px 1px 15px;
        border: .5px solid #ffc201;
        background-color: #ebebeb;
        /*right: 4px;*/
    }
    .has_many_gantt{
        display: inline-block;
        position: absolute;
        right: 10px;
        top: 5px;
        cursor: pointer;
        z-index: 1;
    }
    .vis-item-overflow{
        margin: -2.5px;
    }
    .active{
        display: block/*!important*/;
        z-index: 6;
    }
    .vis-item .vis-item-overflow {
        overflow: unset;
    }
    .vc-sketch {
        left: -178px;
    }
    /* Tooltip on hover*/
    .tooltiptext {
        visibility: hidden;
        background: #dcdcdc;
        color: #000;
        text-align: center;
        border-radius: 6px;
        padding: 5px 10px;
        position: fixed;
        z-index: 1;
    }
    .vis-item:hover .tooltiptext {
        visibility: visible;
    }
    .single-link-to-gantt:hover .tooltiptext{
        visibility: hidden !important;
    }
    .notifications{
        z-index: 999;
    }
    /*.fa-envelope{*/
        /*color: red;*/
    /*}*/
    .fa-envelope{
        display: none;
    }
    .need_to_notify{
        color: red;
        display: inline-block;
    }
</style>
<template lang="pug">
    div
        div.row
            div.col-md-12
                h3.d-inline-block {{ is_custom !== 1 ? $t('gantt.User_planning') : $t('gantt.User_planning_custom') }}: {{ planning_name }}
                button.btn.btn-diga.mr-2.float-right(@click="next_element") Ok
                input.form-control.float-right(v-model="search" style="width: 200px; margin-left: 5px; margin-right: 5px;" type="text" :placeholder="$t('client.Search')")
                select.form-control.float-right(style="width: 200px" v-model="scale")
                    option(:value="0") {{ $t('gantt.Scale') }}
                    option(:value="1") {{ $t('gantt.Day') }}
                    option(:value="2") {{ $t('gantt.Week') }}
                    option(:value="3") {{ $t('gantt.Month') }}
                    option(:value="4") {{ $t('gantt.3_months') }}
                    option(:value="5") {{ $t('gantt.6_months') }}
                    option(:value="6") {{ $t('gantt.Year') }}
                button.btn.btn-diga.mr-2.float-right(v-if="user_not_empty" @click="users_modal_open") {{ is_custom !== 2 ? $t('gantt.Add_user') : $t('gantt.Add_group') }}
                button.btn.btn-diga.mr-2.float-right(v-if="group_not_empty" @click="open_create_task_modal") {{ $t('gantt.Add_task') }}

        div
            timeline(style="margin-bottom: 10px"
                ref="timeline"
                :items="items"
                :groups="groups"
                :options="options"
                @select="select"
                @click="click"
                @rangechange="rangechange"
                @rangechanged="rangechanged"
            )
        #user_modal.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-dialog-centered(role="document")
                .modal-content(v-if="currentUser !== null")
                    .modal-header
                        h2.modal-title#myModalLabel(v-if="is_custom !== 1") {{ currentUser.is_new_user ? $t('gantt.Select_user') : $t('gantt.Edit_user') }}
                        h2.modal-title#myModalLabel(v-if="is_custom === 1") {{ $t('gantt.Insert_user') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body
                        div(v-if="currentUser.is_new_user")
                            fieldset.form-group(:class="{ 'has-error': errors.has('user_id') }")
                                div(v-if="is_custom === 1")
                                    fieldset(:class="{ 'has-error': errors.has('custom_name') }")
                                        label {{ $t('gantt.User') }}
                                        input.form-control(v-model="currentUser.custom_name" value="$t('gantt.User')" name="custom_name", v-validate="'required'", v-bind:data-vv-as="$t('gantt.User').toLowerCase()")
                                        h6.help-block(v-show="errors.has('custom_name')") {{ errors.first('custom_name') }}
                                div(v-else)
                                    select.form-control(v-model="currentUser.user_id" name="user_id", v-validate="'required'", v-bind:data-vv-as="$t('gantt.User').toLowerCase()")
                                        option(v-for="user in users_we_can_add", :value="user.id") {{ user.name }}
                                    h6.help-block(v-show="errors.has('user_id')") {{ errors.first('user_id') }}
                            div.picker-box
                                div
                                    p(style="margin-top: 13px") {{ $t('gantt.Background_color') }}
                                div(style='position: relative')
                                    div.dot(@click="currentUser.show_color = !currentUser.show_color" style="right: 4%" :style="{background: user_color.hex}")
                            div.color-popup(v-if="currentUser.show_color")
                                div(style="height: 23px")
                                    button.close(type="button" v-on:click='currentUser.show_color = false')
                                        span(aria-hidden="true") &times;
                                sketch-picker(v-model="user_color" v-on-clickaway="hide_picker_user" style="top: -180px;")
                            button.btn.btn-diga(@click='add_user') {{ is_custom !== 2 ? $t('gantt.Add_user') : $t('gantt.Add_group') }}
                        div(v-else)
                            div(v-if="is_custom === 1")
                                fieldset.form-group
                                    label {{ $t('gantt.User') }}
                                    input.form-control(v-model="currentUser.custom_name")
                            div(v-else)
                                select.form-control(style="margin-bottom: 10px" v-model="currentUser.selected_id")
                                    option(v-for="user in users_we_can_edit", :value="user.id") {{ user.name }}
                            div.picker-box
                                div
                                    p(style="margin-top: 13px") {{ $t('gantt.Background_color') }}
                                div(style='position: relative')
                                    div.dot(@click="currentUser.show_color = !currentUser.show_color" style="right: 4%" :style="{background: user_color.hex}")
                            div.color-popup(v-if="currentUser.show_color")
                                div(style="height: 23px")
                                    button.close(type="button" v-on:click='currentUser.show_color = false')
                                        span(aria-hidden="true") &times;
                                sketch-picker(v-model="user_color" v-on-clickaway="hide_picker_user" style="top: -180px;")
                            button.btn.btn-secondary(@click='change_user') {{ $t('gantt.Change_user') }}
                            button.btn.btn-danger.ml-2(@click='delete_user') {{ $t('gantt.Delete_user') }}
        #task_modal.modal.fade(tabindex='-1', role="dialog", aria-labelledby='myModalLabel', aria-hidden="true")
            div.modal-dialog.modal-dialog-centered(role="document" :class = '[{"modal-lg": is_custom === 2}]')
                .modal-content(v-if="currentTask !== null")
                    .modal-header
                        h2.modal-title#myModalLabel {{ currentTask.new_task ? $t('gantt.Add_task') : $t('gantt.Edit_task') }}
                        button.close(type="button" data-dismiss="modal" aria-label="Close" v-on:click='close_modal()')
                            span(aria-hidden="true") &times;
                    .modal-body
                        div(:class = '[{"row": is_custom === 2}]')
                            div(:class = '[{"col-6": is_custom === 2}]')
                                div.row
                                    div.col-6
                                        p(style="margin-top: 7px") {{ $t('gantt.Start_date') }}
                                        date-picker(format="YYYY-MM-DD" v-model="currentTask.start_date", :lang="$root.locale", :width="'100%'")
                                    div.col-6
                                        p(style="margin-top: 7px") {{ $t('gantt.End_date') }}
                                        date-picker(format="YYYY-MM-DD" v-model="currentTask.end_date", :lang="$root.locale", :width="'100%'")
                                div.row
                                    div.col-6
                                        p(style="margin-top: 7px") {{ $t('gantt.Start_time') }}
                                        TimePicker( placeholder="" v-model="currentTask.startTime" :picker-options="{ start: '00:00', step: '00:15',end: '24:00' }")
                                    div.col-6
                                        p(style="margin-top: 7px") {{ $t('gantt.End_time') }}
                                        TimePicker(ref="el_end_time" placeholder="" v-model="currentTask.finishTime" :picker-options="time_select_option")
                                    div.color-popup(v-if="currentTask.show_color")
                                        sketch-picker(v-if="currentTask.show_color" v-model="task_color" v-on-clickaway="hide_picker_task")
                                label {{ $t('gantt.User') }}
                                select.form-control(style="margin-bottom: 10px" v-model="currentTask.task_for_user")
                                    option(v-for="usr in this.groups", :value="usr.id") {{ usr.content }}
                                fieldset.form-group(:class="{ 'has-error': errors.has('task_name') }")
                                    label {{ $t('gantt.Task_name') }}
                                    div(style="position:relative;")
                                        input.form-control.mr-2(v-model="currentTask.task_name" name="task_name", v-validate="'required'", v-bind:data-vv-as="$t('gantt.Task_name').toLowerCase()")
                                        div.dot(@click="currentTask.show_color = !currentTask.show_color" data-backdrop="false" :style="{background: task_color.hex}")
                                    h6.help-block(v-show="errors.has('task_name')") {{ errors.first('task_name') }}
                                div(v-if="is_custom === 2")
                                    v-select(style="width: 100%;",
                                        v-model="currentTask.estimate_name",
                                        :debounce='250',
                                        :on-search='get_base_options',
                                        :on-change='base_select',
                                        :options='bases',
                                        :placeholder="$t('estimate.Choose_estimate')")
                                    div(v-if="currentTask.estimate_id !== null")
                                        fieldset.form-group.mt-1
                                            label {{ $t('calendar.Amount') }}
                                            input.form-control.mr-2(v-model="currentTask.est_price_with_currency", readonly)
                                    fieldset.form-group
                                        label {{ $t('gantt.Task_description') }}
                                        textarea.form-control.mr-2(v-model="currentTask.task_description")
                                    fieldset.form-group
                                        label {{ $t('estimate.Subcontracting') }}
                                        div(style="width:120px;")
                                            bootstrap-toggle(data-size="mini" v-model="currentTask.is_subcontract", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                                    fieldset
                                        label {{ $t('estimate.My_Percent') }}
                                        input.form-control.mr-2(style="margin-bottom: 10px" v-model="currentTask.company_percent" type='number', min="1", max="100" :disabled="!currentTask.is_subcontract")
                            div(:class = '[{"col-6": is_custom === 2}]', v-if='is_custom === 2')
                                p(style="margin-top: 7px") {{ $t('gantt.Payment_steps') }}
                                fieldset.form-group
                                    label {{ $t('estimate.Email_auto_send') }}
                                    div(style="width:120px;")
                                        bootstrap-toggle(data-size="mini" v-model="currentTask.email_auto_send", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")
                                //- fieldset.form-group(v-if="currentTask.email_auto_send === true")
                                //-     label {{ $t('estimate.Attach_invoice') }}
                                //-     div(style="width:120px;")
                                //-         bootstrap-toggle(data-size="mini" v-model="currentTask.attach_invoice", :options="{ on: $t('template.Yes'), off: $t('template.No')}", data-width="120", data-height="38", data-onstyle="default")


                                table.table.table-striped.w-100
                                    thead
                                        tr
                                            th {{ $t('project.Name') }}
                                            th %
                                            th {{ $t('service.Date') }}
                                            th(v-if="currentTask.email_auto_send === true") {{$t('template.choose_email_template')}}
                                    tbody(v-if='currentTask.pay_stages !== null')
                                        tr(v-for="(payment,i) in currentTask.pay_stages")
                                            td(style="padding-right: 15px") {{ payment.text }}
                                            td(style="padding-right: 15px") {{ payment.percent }}
                                            td(style="padding-right: 15px" :class="{ 'has-error': errors.has('payment.payment_date'+i) }")
                                                date-picker(format="YYYY-MM-DD" v-model="payment.payment_date", :lang="$root.locale", :width="'100%'" value="$t('service.Date')" :name="'payment.payment_date'+i", v-validate="'required'", v-bind:data-vv-as="$t('service.Date').toLowerCase()")
                                                h6.help-block(v-show="errors.has('payment.payment_date'+i)") {{ errors.first('payment.payment_date'+i) }}
                                            td(v-if="currentTask.email_auto_send === true")
                                                select.form-control.mx-2.mb-2(v-model="payment.email_template_id", style="flex: 2;min-width: 150px;")
                                                    option(v-for="email_template in email_templates" v-bind:value="email_template.id" v-text="email_template.name")
                                    tbody(v-else)
                                        tr
                                            td(colspan="3") {{ $t('gantt.Estimate_not_selected') }}
                        div(v-if="currentTask.new_task")
                            button.btn.btn-diga(@click='save_new_task') {{ $t('gantt.Save_task') }}
                        div(v-else)
                            button.btn.btn-secondary(@click='change_task') {{ $t('gantt.Change_task') }}
                            button.btn.btn-danger.ml-2(@click='delete_task') {{ $t('gantt.Delete_task') }}
</template>

<script>

import moment from 'moment';
import {Timeline} from 'vue2vis';
import {mapGetters} from "vuex";
import TimePicker from 'element-ui/lib/time-select';

require("element-ui/lib/theme-chalk/index.css");

export default {
    props: ['id'],
    data() {
        return {
            planning_name: '',
            group_not_empty: false,
            scale: 0,
            user_not_empty: true,
            selected_task_id: '',
            selected_row: null,
            before_change: null,
            links_to_gantt: false,
            sketch_picker: '',
            task_color: { hex: null},
            user_color: { hex: null},
            //
            currentTask: null,
            currentUser: null,
            //
            items: [],
            groups: [],
            options: {
                orientation: 'top',
                showTooltips: false,
                showCurrentTime: true,
                start: moment().startOf('day'),
                end: moment().endOf('day'),
                editable: true,
                groupEditable: true,
                verticalScroll: true,
                height: 0,
                onAdd: (() => {
                    let $this = this;
                    return function (item, callback) {
                        if ($this.groups.length > 0) {
                            let scale = $this.$refs.timeline.timeline.body.util.getScale();
                            $this.open_create_task_modal(item.group, item.start, scale);
                        }
                    }
                })(),
                onMove: (() => {
                    let $this = this;
                    return function (item, callback) {
                        if (confirm($this.$root.$t('gantt.Move_task'))) {
                            $this.update_on_move(item);
                        }
                        else{
                            callback(null);
                        }   
                    }
                })(),
                onRemove: (() => {
                    let $this = this;
                    return function (item, callback) {
                        $this.edit_task(item);
                    }
                })(),
                moment: function(date) {
                    return moment(date);
                },
                template: function (item, element, data) {
                    if(item.gantt_chart !== null){
                        var gantt_arr_len = JSON.parse(JSON.stringify(item.gantt_chart)).length;
                    }
                    let string = '<div style="overflow: hidden;"><p class="content">' + item.content + ' <span class="tooltiptext">'+ item.content +'</span> <i class="fa fa-envelope ' + item.id +'"></i></p></div>';
                    if(item.estimate_id !== null && gantt_arr_len != 0){
                            if(item.gantt_chart.length > 1){
                                var links_to_gant_chart = '';
                                for(let i = 0; i < item.gantt_chart.length; i++){
                                    links_to_gant_chart = links_to_gant_chart + '<a class="single-link-to-gantt '+ item.id +'-bar" href="/estimate_plannings/' + item.gantt_chart[i].id+ '" target="_blank"><p>' + item.gantt_chart[i].name + '</p></a>';
                                }
                                string += '<p class="has_many_gantt '+ item.id+'"><i class="fa fa-external-link"></i></p><div class="gantt-links-box '+ item.id +'-bar">' + links_to_gant_chart + '</div>';
                            } else {
                                string += '<a class="has_many_gantt" href=/estimate_plannings/' + item.gantt_chart[0].id + ' target="_blank"><i class="fa fa-external-link"></i></a>';
                            }
                    }
                    return string;
                },
            },
            is_custom: null,
            bases: [],
            time_select_option: {
                start: '00:00',
                step: '00:15',
                end: '24:00',
            },
            search: "",
            elements: [],
            current_element: null,
            email_templates: []
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.User_planning');
        this.load_user_planning();
        this.recalculate_timeline_height(window.innerHeight);
        this.$nextTick(() => {
            this.attach_listeners();
            // this.notification_trigger();
            window.addEventListener('resize', () => {
                this.recalculate_timeline_height(window.innerHeight);
            });
        });
        this.get_email_templates();
    },
    created(){
        let $this = this;
        $('#user_modal').on('hidden.bs.modal', function (e) {
            $this.currentUser = null;
        });
        if(this.is_custom !== 2){
            $('.fa-envelope').css('display','none !important');
        }
        $(document).on('mouseenter', '.vis-item-overflow', function(){
            var offset = $(this).offset();
            var topOffset = $(this).offset().top- $(window).scrollTop();
            var divOffest = $(".tooltiptext").outerHeight();
            $(".tooltiptext").css({
                position: "fixed",
                top: (topOffset - divOffest - 10)+ "px",
                left: (offset.left) + "px",
            });
        });

        $(document).on('click', '.has_many_gantt', function(){
            var offset = $(this).offset();
            var topOffset = $(this).offset().top- $(window).scrollTop();
            $('div.' +$(this)[0].classList[1]+'-bar').toggleClass('active');
            $('a.' +$(this)[0].classList[1]+'-bar').toggleClass('active');
            var divOffest = $(".gantt-links-box").outerHeight();

            $(".gantt-links-box").css({
                position: "fixed",
                top: (topOffset - divOffest - 10)+ "px",
                left: (offset.left) + "px",
            });
        });
        $(document).on('click', '.single-link-to-gantt', function(){
            $(".single-link-to-gantt").removeClass('active');
            $(".gantt-links-box").removeClass('active');
        });
    },
    beforeDestroy(){
        this.deattach_notification_trigger();
    },
    components: {
        'timeline': Timeline, TimePicker,
    },
    watch: {
        scale(newVal){
            switch (newVal){
            case 0:
                break;
            case 1:
                this.options.start = moment().startOf('day');
                this.options.end = moment().endOf('day');
                this.scale = 0;
                break;
            case 2:
                this.options.start = moment().startOf('week');
                this.options.end = moment().endOf('week');
                this.scale = 0;
                break;
            case 3:
                this.options.start = moment().startOf('month');
                this.options.end = moment().endOf('month');
                this.scale = 0;
                break;
            case 4:
                this.options.start = moment().subtract(1, 'months').startOf('month');
                this.options.end = moment().add(1, 'months').endOf('month');
                this.scale = 0;
                break;
            case 5:
                this.options.start = moment().subtract(2.5, 'months').startOf('month');
                this.options.end = moment().add(2.5, 'months').endOf('month');
                this.scale = 0;
                break;
            case 6:
                this.options.start = moment().startOf('year');
                this.options.end = moment().endOf('year');
                this.scale = 0;
                break;
            }
        },
        search(newVal){
            this.elements = [];
            if (this.search !== ""){
                this.items.forEach(i => {
                    if (i.content.includes(newVal)){
                        this.elements.push(i);
                    }
                });

                if (this.elements.length > 0){
                    this.current_element = 0;
                    this.$refs.timeline.focus(this.elements[0].id);
                    this.$refs.timeline.setSelection(this.elements[0].id);
                }
            }
        },
    },
    computed: {
        ...mapGetters({
            usr: 'getUsers',
            usr_id: 'getUsersById',
            grp: 'getGroups',
            grp_id: 'getGroupsById',
        }),
        users_we_can_add(){
            let load = [];
            if(this.is_custom === 0){load = this.usr} else {load = this.grp}
            return load.filter((user) => {
                return this.groups.find(group => group.user_id === user.id) === undefined;
            })
        },
        users_we_can_edit(){
            let load = [];
            if(this.is_custom === 0){load = this.usr} else {load = this.grp}
            return load.filter((user) => {
                return this.groups.find(group => group.user_id === user.id) === undefined || user.id === this.currentUser.selected_id;
            })
        },
    },
    methods: {
        get_email_templates(){
            this.$http.get('/api/email_templates').then(res => {
                if (res.data.errcode === 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.email_templates = res.data;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });            
        },
        next_element(){
            if (this.current_element === (this.elements.length - 1)){
                this.current_element = 0;
            }
            else{
                this.current_element += 1;
            }

            this.$refs.timeline.focus(this.elements[this.current_element].id);
            this.$refs.timeline.setSelection(this.elements[this.current_element].id);
        },
        notification_trigger(){
            let $this = this;
            $(document).on('click', 'i.need_to_notify', function(event){
                event.stopPropagation();
                var result = $this.items.filter(obj => {
                    return obj.id == $(this)[0].classList[2];
                });
                $this.notify_construction_managers(result);
                result[0].notification_about_changes = 1;
                $(this).removeClass('need_to_notify');
            });
        },
        deattach_notification_trigger(){
            $(document).off('click', 'i.need_to_notify');
        },
        rangechanged(){
            if(this.is_custom === 2){
                this.items.map(function(item) {
                    if(item.notification_about_changes === "0"){
                        $("." + item.id).addClass('need_to_notify');
                    }
                });
            }
        },
        recalculate_timeline_height(newHeight){
            this.options.height = newHeight - 220;
        },
        load_user_planning(){
            this.$http.get('/api/user_plannings/' + this.id).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    let $this = this;
                    this.is_custom = res.data.plan.is_custom;
                    this.planning_name = res.data.plan.name;
                    res.data.plan.users.forEach(u => {
                        $this.add_group_to_options(u.id, u.user_id, u.color, u.content);
                        u.tasks.forEach(t => {
                            let new_content = t.estimate === null ? t.title : t.estimate.service.estimate_number + ' | ' + (Math.round(t.estimate.price * 100) / 100) + ' ' + this.$root.global_settings.currency + ' | ' + t.description + ' | ' + t.title;
                            this.items.push({
                                id: t.id,
                                group: t.user_planning_user_id,
                                content: new_content,
                                start: t.start,
                                end: t.end,
                                description: t.description,
                                style: 'background-color:' + t.color,
                                estimate_id: t.estimate_id,
                                estimate_name: t.estimate === null ? null : t.estimate.service.estimate_number,
                                pay_stages: t.estimate === null ? null : t.estimate.estimate_pay_stages,
                                gantt_chart: t.gantt,
                                estimate_price: t.estimate !== null ? t.estimate.price : null,
                                notification_about_changes: t.notification_about_changes,
                                is_subcontract: t.is_subcontract,
                                company_percent: t.company_percent,
                                email_auto_send: t.email_auto_send,
                                attach_invoice: t.attach_invoice,
                            });
                            if(t.notification_about_changes === "0" && this.is_custom === 2){
                                $( document ).ready(function() {
                                    $("." + t.id).addClass('need_to_notify');
                                });
                            }

                        });
                        this.groups.length !== 0 ? this.group_not_empty = true : this.group_not_empty = false;
                    });
                    this.notification_trigger();
                    this.attach_listeners();
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },

        select(){
            let a = document.getElementsByClassName("vis-delete")["0"];
            if (a != null){
                document.getElementsByClassName("vis-delete")["0"].title = '';
            }
        },
        click(){
            $(".single-link-to-gantt").removeClass('active');
            $(".gantt-links-box").removeClass('active');
        },
        rangechange(){
            $(".single-link-to-gantt").removeClass('active');
            $(".gantt-links-box").removeClass('active');
        },
        hide_picker_task(){
          this.currentTask.show_color = false;
        },
        hide_picker_user(){
            this.currentUser.show_color = false;
        },
        users_modal_open(){
            this.currentUser = {
                is_new_user: true,
                show_color: false,
                selected_id: '',
                custom_name: '',
                color: '#94c5f6',
                user_id: this.users_we_can_add[0].id,
            };
            this.user_color.hex = '#94c5f6';
            $('#user_modal').modal('show');
        },
        add_group_to_options(id, user_id, color, custom_name){
            let name;
            if (this.is_custom === 1){
                name = custom_name;
                user_id = null;
            } else if(this.is_custom === 0) {
                name = this.usr_id[user_id].name;
            } else if(this.is_custom === 2) {
                name = this.grp_id[user_id].name;
            }
            this.groups.push({
                id: id,
                user_id: user_id,
                content: name,
                style: 'background-color:' + color,
            });
            this.$refs.timeline.setGroups(this.groups);
            if (this.is_custom !== 1){
                if (this.users_we_can_add.length !== 0){
                    this.user_not_empty = true;
                } else {
                    this.user_not_empty = false;
                }
            }
        },
        add_user(){
            let validate = '';
            if (this.is_custom === 1){
                validate = 'custom_name';
            } else {
                validate = 'user_id';
            }
            this.$validator.validate(validate).then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                let curr_urs_id = this.currentUser.user_id;
                if (this.is_custom === 1){
                    curr_urs_id = null;
                }
                this.$http.post('/api/user_planning_users', {
                    user_id: curr_urs_id,
                    user_planning_id: this.id,
                    color: this.user_color.hex,
                    content: this.currentUser.custom_name,
                }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.add_group_to_options(res.data.id, this.currentUser.user_id, this.user_color.hex, this.currentUser.custom_name);
                        this.$nextTick(() => {
                            this.attach_listeners();
                        });
                        this.groups.length !== 0 ? this.group_not_empty = true : this.group_not_empty = false;
                        this.currentUser = null;
                        $('#user_modal').modal('hide');
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            });
        },
        open_user(username){
            this.$nextTick(() => {
                // TRIM used here because sometimes name has a space at the end and function 'find' can't find the correct object
                let group = this.groups.find(g => g.content.trim() === username);

                this.selected_row = group.id;
                this._beforeEditingCache = group.user_id;
                this.before_change = this._beforeEditingCache;

                this.currentUser = {
                    is_new_user: false,
                    show_color: false,
                    selected_id: group.user_id,
                    custom_name: username,
                    color: group.style.substring(17, 24),
                    user_id: this.users_we_can_add[0].id,
                };
                this.user_color.hex = group.style.substring(17, 24);
                $('#user_modal').modal('show');
            })
        },
        change_user(){
            let $this = this;
            let usr_id = ''; let usr_name = '';
            if (this.is_custom == 1){
                usr_id = null;
            } else {
                let user_or_group;
                if(this.is_custom === 0){
                    user_or_group = this.usr;
                } else {
                    user_or_group = this.grp;
                }
                let selected_new_user = user_or_group.filter(function (el) {
                    return el.id === $this.currentUser.selected_id;
                });
                this.groups = this.groups.filter((user) => {
                    return user.user_id !== this.before_change;
                });
                usr_id = selected_new_user[0].id;
                usr_name = selected_new_user[0].name;
            }
            this.$http.put('/api/user_planning_users/' + this.selected_row, {
                // id: this.selected_row,
                user_id: usr_id,
                color: this.user_color.hex,
                content: this.currentUser.custom_name,
            }).then(res => {
                if (res.data.errcode === 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    if (this.is_custom === 1){
                        for (let i = 0; i < this.groups.length; i++){
                            if (this.groups[i].id === this.selected_row){
                                this.groups[i].user_id = null;
                                this.groups[i].content = this.currentUser.custom_name;
                                this.groups[i].style = 'background-color:' + this.user_color.hex;
                            }
                        }
                    } else {
                        this.groups.push({id: this.selected_row, user_id: usr_id, content: usr_name, style: 'background-color:' + this.user_color.hex });
                    }
                    this.$nextTick(() => {
                        this.attach_listeners();
                    });
                    this.$refs.timeline.setGroups(this.groups);
                    this.currentUser = null;
                    $('#user_modal').modal('hide');
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        delete_user(){
            if (confirm(this.$root.$t('gantt.User_task_delete'))){
                this.$http.delete('/api/user_planning_users/' + this.selected_row).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.groups = this.groups.filter((g) => {
                            return g.id !== this.selected_row;
                        });
                        this.items = this.items.filter((item) => {
                            return item.group !== this.selected_row;
                        });
                        this.$refs.timeline.setGroups(this.groups);
                        this.attach_listeners();
                        this.groups.length != 0 ? this.group_not_empty = true : this.group_not_empty = false;
                        this.currentUser = null;
                        $('#user_modal').modal('hide');
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },
        open_create_task_modal(group = null, start = null, scale = null){
            this.currentTask = {
                style: '',
                estimate_name: null,
                estimate_id: null,
                new_task: true,
                show_color: false,
                task_description: '',
                task_name: '',
                task_for_user: '',
                pay_stages: null,
                start_date: moment().format('YYYY-MM-DD'),
                end_date: moment().add(1, 'hours').format('YYYY-MM-DD'),
                startTime: moment().format('HH:mm'),
                finishTime: moment().add(1, 'hours').format('HH:mm'),
                gantt_chart: null,
                estimate_price: null,
                notification_about_changes: 1,
                is_subcontract: false,
                company_percent: 100,
                email_auto_send: false,
                attach_invoice: false,
            };
            this.task_color.hex = this.$root.settings.default_color_of_roadmap_task;
            this.currentTask.task_for_user = this.groups[0].id;

            if (group && start && scale){
                this.currentTask.task_name = this.$root.$t("gantt.New_task");
                this.currentTask.task_for_user = group;

                this.currentTask.start_date = moment(start).format('YYYY-MM-DD');
                this.currentTask.end_date = moment(start).add(1, 'hours').format('YYYY-MM-DD');

                if (scale === 'hour' || scale === 'minute'){
                    this.currentTask.startTime = moment(start).format('HH:mm');
                    this.currentTask.finishTime = moment(start).add(1, 'hours').format('HH:mm');
                } else {
                    this.currentTask.startTime =  moment().startOf('day').format('HH:mm');
                    this.currentTask.finishTime =  moment().endOf('day').format('HH:mm');
                }
            }
            $('#task_modal').modal('show');
        },
        save_new_task(){
            this.$validator.validate().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                if(this.currentTask.pay_stages !== null){
                    for(let i = 0; i < this.currentTask.pay_stages.length; i++){
                        this.currentTask.pay_stages[i].payment_date = moment(this.currentTask.pay_stages[i].payment_date).format('YYYY-MM-DD');
                    }
                }
                let start = moment(this.currentTask.start_date).format('YYYY-MM-DD') + ' ' + this.currentTask.startTime;
                let end = moment(this.currentTask.end_date).format('YYYY-MM-DD') + ' ' + this.currentTask.finishTime;
                let task_name = this.currentTask.estimate_name !== null ?  this.currentTask.estimate_name + ' | ' + (Math.round(this.currentTask.estimate_price * 100) / 100) + ' ' + this.$root.global_settings.currency + ' | ' + this.currentTask.task_description + ' | ' + this.currentTask.task_name : this.currentTask.task_name;
                this.$http.post('/api/user_planning_user_tasks', {
                    user_planning_user_id: this.currentTask.task_for_user,
                    start: start,
                    end: end,
                    title: this.currentTask.task_name,
                    description: this.currentTask.task_description,
                    // color: this.currentTask.task_color,
                    color: this.task_color.hex,
                    estimate_id: this.currentTask.estimate_id,
                    pay_stages: this.currentTask.pay_stages,
                    gantt_chart: this.currentTask.gantt_chart,
                    notification_about_changes: 1,
                    is_subcontract: this.currentTask.is_subcontract,
                    company_percent: this.currentTask.company_percent,
                    email_auto_send: this.currentTask.email_auto_send,
                    attach_invoice: this.currentTask.attach_invoice,
                }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        if (this.task_for_user !== "") {
                            this.items.push({
                                id: res.data.id,
                                group: this.currentTask.task_for_user,
                                content: task_name,
                                start: start,
                                end: end,
                                description: this.currentTask.task_description,
                                style: 'background-color:' + this.task_color.hex,
                                estimate_name: this.currentTask.estimate_name,
                                estimate_id: this.currentTask.estimate_id,
                                pay_stages: this.currentTask.pay_stages,
                                gantt_chart: this.currentTask.gantt_chart !== null ? this.currentTask.gantt_chart : null,
                                estimate_price: this.currentTask.estimate_price,
                                notification_about_changes: 1,
                                is_subcontract: this.currentTask.is_subcontract,
                                company_percent: this.currentTask.company_percent,
                                email_auto_send: this.currentTask.email_auto_send,
                                attach_invoice: this.currentTask.attach_invoice,
                            });
                        }
                        // this.notification_trigger();
                        this.currentTask.show_color = false;
                        $('#task_modal').modal('hide');
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            });
        },
        edit_task(task){
            this.currentTask = {
                new_task: false,
                show_color: false,
                task_description: task.description,
                task_name: task.content.split('|').length > 1 ? task.content.split('|')[3].trim() : task.content,
                task_for_user: task.group,
                start_date: task.start,
                end_date: task.end,
                task_color: task.style.slice(17, 24),
                estimate_name: task.estimate_name,
                estimate_id: task.estimate_id,
                startTime: moment(task.start).format('HH:mm'),
                finishTime: moment(task.end).format('HH:mm'),
                pay_stages: task.pay_stages,
                gantt_chart: task.gantt_chart,
                estimate_price: task.estimate_price,
                est_price_with_currency: (Math.round(task.estimate_price * 100) / 100) + ' ' + this.$root.global_settings.currency,
                notification_about_changes: task.notification_about_changes,
                is_subcontract: task.is_subcontract,
                company_percent: task.company_percent,
                email_auto_send: task.email_auto_send,
                attach_invoice: task.attach_invoice,
            };
            this.task_color.hex = task.style.slice(17, 24);
            this.selected_task_id = task.id;

            $('#task_modal').modal('show');
        },
        change_task(){
            this.$validator.validate().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                if (confirm(this.is_custom === 2 ? this.$root.$t('gantt.Task_edit_with_events') : this.$root.$t('gantt.Save_edited_task'))) {
                    let temp = 0;
                    for (let i = 0; i < this.items.length; i++) {
                        if (this.items[i].id === this.selected_task_id) {
                            temp = i;
                        }
                    }
                    if (this.currentTask.pay_stages !== null) {
                        for (let i = 0; i < this.currentTask.pay_stages.length; i++) {
                            this.currentTask.pay_stages[i].payment_date = moment(this.currentTask.pay_stages[i].payment_date).format('YYYY-MM-DD');
                        }
                    }
                    this.$http.put('/api/user_planning_user_tasks/' + this.selected_task_id, {
                        user_planning_user_id: this.currentTask.task_for_user,
                        start: moment(this.currentTask.start_date).format('YYYY-MM-DD') + ' ' + this.currentTask.startTime,
                        end: moment(this.currentTask.end_date).format('YYYY-MM-DD') + ' ' + this.currentTask.finishTime,
                        title: this.currentTask.task_name,
                        description: this.currentTask.task_description,
                        color: this.task_color.hex,
                        estimate_id: this.currentTask.estimate_id,
                        pay_stages: this.currentTask.pay_stages,
                        notification_about_changes: 1,
                        is_subcontract: this.currentTask.is_subcontract,
                        company_percent: this.currentTask.company_percent,
                        email_auto_send: this.currentTask.email_auto_send,
                        attach_invoice: this.currentTask.attach_invoice
                    }).then(res => {
                        if (res.data.errcode == 1) {
                            this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        } else {
                            let task_name = this.currentTask.estimate_name !== null ? this.currentTask.estimate_name + ' | ' + (Math.round(this.currentTask.estimate_price * 100) / 100) + ' ' + this.$root.global_settings.currency + ' | ' + this.currentTask.task_description + ' | ' + this.currentTask.task_name : this.currentTask.task_name;
                            this.items[temp].group = this.currentTask.task_for_user;
                            this.items[temp].content = task_name;
                            this.items[temp].start = moment(this.currentTask.start_date).format('YYYY-MM-DD') + ' ' + this.currentTask.startTime;
                            this.items[temp].end = moment(this.currentTask.end_date).format('YYYY-MM-DD') + ' ' + this.currentTask.finishTime;
                            this.items[temp].description = this.currentTask.task_description;
                            this.items[temp].style = 'background-color:' + this.task_color.hex;
                            this.items[temp].estimate_name = this.currentTask.estimate_name;
                            this.items[temp].estimate_id = this.currentTask.estimate_id;
                            this.items[temp].pay_stages = this.currentTask.pay_stages;
                            this.items[temp].gantt_chart = this.currentTask.gantt_chart;
                            this.items[temp].is_subcontract = this.currentTask.is_subcontract;
                            this.items[temp].company_percent = this.currentTask.company_percent;
                            this.items[temp].email_auto_send = this.currentTask.email_auto_send;
                            this.items[temp].attach_invoice = this.currentTask.attach_invoice;
                            $('#task_modal').modal('hide');
                        }
                    }, res => {
                        this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    });
                }
            });
        },
        delete_task(){
            if (confirm(this.is_custom === 2 ? this.$root.$t('gantt.Task_deleted_with_events') : this.$root.$t('gantt.Task_delete'))){
                this.$http.delete('/api/user_planning_user_tasks/' + this.selected_task_id).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.items = this.items.filter((user) => {
                            return user.id !== this.selected_task_id;
                        });
                        this.$refs.timeline.setGroups(this.groups);
                        this.attach_listeners();
                        $('#task_modal').modal('hide');
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },
        close_modal(){
            $('#task_modal').modal('hide');
            $('#user_modal').modal('hide');
        },
        update_on_move(task){
            if(this.is_custom === 2 && task.estimate_id !== null){
                $("." + task.id).addClass('need_to_notify');
            }
            // This block changes the dates of payment steps automatically in move if settings allows it.
            if(this.$root.settings.make_auto_calculation_for_payment_steps === "1" && task.pay_stages !== null){
                if(task.pay_stages.length === 4){
                    task.pay_stages[1].payment_date = moment(task.start).format('YYYY-MM-DD');
                    task.pay_stages[3].payment_date = moment(task.end).format('YYYY-MM-DD');

                    var start = moment(task.start);
                    var end = moment(task.end);
                    var mid = moment(start).add(Math.round((end.diff(start,'days')/2)), 'd').format('YYYY-MM-DD');
                    if(moment(mid).format('dd') === 'Su'){
                        mid = moment(mid).add(1, 'd');
                    } else if(moment(mid).format('dd') === 'Sa'){
                        mid = moment(mid).subtract(1, 'days');
                    }
                    task.pay_stages[2].payment_date = mid;
                }
            }
            let temp = 0;
            for (let i = 0; i < this.items.length; i++){
                if (this.items[i].id === task.id){
                    temp = i;
                }
            }
            this.selected_task_id = task.id;
            this.$http.put('/api/user_planning_user_tasks/' + this.selected_task_id, {
                user_planning_user_id: task.group,
                start: moment(task.start).format('YYYY-MM-DD HH:mm'),
                end: moment(task.end).format('YYYY-MM-DD HH:mm'),
                pay_stages: task.pay_stages !== null ? task.pay_stages : null,
                notification_about_changes: 0,
            }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.items[temp].start = task.start;
                    this.items[temp].end = task.end;
                    this.items[temp].group = task.group;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
        attach_listeners(){
            let $this = this;
            $("div").find('.vis-label').off('click').click(function () {
                $this.open_user($(this)[0].innerText);
            })
        },
        count_sundays(days, start_date){
            if(moment(start_date).add(days, 'd').format('dd') === 'Su'){
                days = days + 1;
            }
            //This function calculates how many Sundays there are in given period of time it receives starting point (start date) and adds days using moment, then converts it into a date.
            var start = moment(start_date),
                end   = moment(start).add(days, 'd'),
                day   = 0;// Sunday
            var result = [];
            var current = start.clone();

            while (current.day(7 + day).isBefore(end)) {
                result.push(current.clone());
            }

            // Counts
            var start_gap = moment(end);
            var end_gap = moment(end).add(result.length, 'd');

            var result_gap = [];
            var current_gap = start_gap.clone();

            while (current_gap.day(7 + day).isBefore(end_gap)) {
                result_gap.push(current_gap.clone());
            }
            this.currentTask.end_date = moment(end_gap).add(result_gap.length, 'd');
            if(moment(this.currentTask.end_date).format('dd') === 'Su'){
                //If the last day of calculated date is sunday we add one more day to make it monday as a finishing date of a project or task.
                this.currentTask.end_date = moment(this.currentTask.end_date).add(1, 'd').format('YYYY-MM-DD');
            }
            // Counts difference between two dates in days:
            // var a = moment(this.currentTask.start_date);
            // var b = moment(this.currentTask.end_date);
        },
        find_mid_date(){
            var start = moment(this.currentTask.start_date);
            var end = moment(this.currentTask.end_date);
            var mid = moment(start).add(Math.round((end.diff(start,'days')/2)), 'd').format('YYYY-MM-DD');
            if(moment(mid).format('dd') === 'Su'){
                mid = moment(mid).add(1, 'd');
            } else if(moment(mid).format('dd') === 'Sa'){
                mid = moment(mid).subtract(1, 'days');
            }
            if(this.currentTask.pay_stages.length === 4){
                this.currentTask.pay_stages[2].payment_date = mid;
            }
        },
        get_base_options(search, loading) {
            loading(true);
            this.$http.get('/api/user_plannings?search=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.forEach(function(i){
                    processedData.push({'label': $this.$root.estimate_number(i), 'value': i.id, 'pay_stages': i.estimate_pay_stages, 'gantts': i.gantts, 'address' : i.service.address, 'estimate_price' : i.price, 'deadline' : i.deadline});
                });
                this.bases = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        base_select(res){
            if(res === null){
                this.currentTask.pay_stages = null;
                this.currentTask.estimate_id = null;
                this.currentTask.estimate_name = null;
                this.currentTask.task_description = null;
                this.currentTask.gantt_chart = null;
                this.currentTask.estimate_price = null;
                this.currentTask.est_price_with_currency = null;
                this.currentTask.end_date = this.currentTask.start_date;
            }
            if (typeof res === 'object' && res !== null) {
                let flag = false;

                this.items.forEach(i => {
                    if (parseInt(i.estimate_id) === parseInt(res.value)){
                        flag = true;
                    }
                });
                if (flag === true){
                    this.$toastr.e(this.$root.$t("estimate.This_estimate_is_already_in_use"), this.$root.$t("template.Error"));
                }
                else{
                    this.currentTask.estimate_name = res.label;
                    this.currentTask.estimate_id = res.value;
                    this.currentTask.pay_stages = res.pay_stages;
                    this.currentTask.gantt_chart = res.gantts;
                    this.currentTask.task_description = res.address;
                    this.currentTask.estimate_price = res.estimate_price;
                    this.currentTask.est_price_with_currency = (Math.round(res.estimate_price * 100) / 100) + ' ' + this.$root.global_settings.currency;
                    if(this.$root.settings.make_auto_calculation_for_payment_steps === "1"){
                        this.count_sundays(res.deadline, this.currentTask.start_date);
                        if(this.currentTask.pay_stages.length === 4){
                            this.currentTask.pay_stages[1].payment_date = this.currentTask.start_date;
                            this.currentTask.pay_stages[3].payment_date = this.currentTask.end_date;
                            this.find_mid_date();
                        }
                    }
                }
            }
        },
        notify_construction_managers(changed_task){
            if(changed_task[0].estimate_id !== null){
                this.$http.post('/api/user_planning_user_tasks/inform_construction_managers', {
                    estimate_id: changed_task[0].estimate_id,
                    estimate_name: changed_task[0].estimate_name,
                    roadmap_name: this.planning_name,
                    roadmap_id: this.id,
                    task_id: changed_task[0].id,
                }).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            }
        },

    },
}
</script>