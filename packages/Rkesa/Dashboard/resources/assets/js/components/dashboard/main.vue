<style>
    .stage-arrow{
        font-size: 20px;
        height: 330px;
        line-height: 300px;
        width: 30px;
        display: block;
        text-align: center;
        cursor: pointer;
    }
    .stage-arrow:hover,
    .stage-arrow:focus{
        background-color: #eee;
    }
    .stage-entity-wrapper{
        overflow: hidden;
        white-space: nowrap;
        position: absolute;
        top: 0;
        padding: 10px;
        display: flex;
    }
    .modal.fade.in {
        top: 0;
    }
    .dashboard-wrapper .modal-body{
        max-height: 500px;
        overflow-y: scroll;
    }
    svg.highcharts-root{
        font-family: inherit !important;
    }
    .highcharts-title, .table-title{
        text-transform: uppercase;
        font-size: 18px;
    }
    .highcharts-subtitle, .table-subtitle{
        font-size: 14px;
    }
    .use_range_container .toggle{
        width: 100% !important;
    }
    .header-first-third{
        min-width:210px;
        flex:1;
        margin: 0 10px 10px;
    }
    .header-second{
        flex:2;
        line-height: 80px;
        margin: 0 10px;
        text-align: center;
        white-space: nowrap;
        text-transform: uppercase;
        font-weight: bold;
        font-size: 20px;
    }
    .fc-toolbar.fc-header-toolbar {
        margin-bottom: 1em;
        display: none;
    }
</style>

<template lang="pug">
    section.dashboard-wrapper
        div(v-if="loading", style="text-align: center;padding-top: 15%;")
            div.loader
        div(v-else)
            template(v-if="$root.user.dashboard_id !== 0 && $root.user.dashboard_id !== null")
                entities-header
                table(style="width:100%;table-layout: fixed;")
                    tr
                        td(style="width:35px;padding-right:5px;")
                            div.stage-arrow.diga-container(v-on:click="to_left()")
                                i.fa.fa-chevron-left
                        td(style="position: relative; overflow: hidden;height: 355px;" ref="line_wrapper")
                            div.stage-entity-wrapper(v-bind:style="{'left': animated_x+'px'}" ref="line")
                                stage-entity(v-if="entity.state.type == 0 && entity.hide == 0" v-for="entity, key, index in ordered_entities", :key="key", :entity="entity", v-on:full="full")
                        td(style="width:35px;padding-left:5px;")
                            div.stage-arrow.diga-container(v-on:click="to_right()")
                                i.fa.fa-chevron-right
                div.modal.fade#modal-full-list(tabindex="-1" role="dialog")
                    div.modal-dialog
                        div.modal-content(v-if="current_entity != null")
                            div.modal-header
                                h4.modal-title {{ current_entity.state.name }}
                                button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                                    span(aria-hidden="true") &times;
                            div.modal-body
                                div.form-horizontal(role="form")
                                    table.table.table-bordered.table-striped(style="margin-bottom:0;height: 200px;")
                                        thead
                                            tr
                                                th #
                                                th(v-for="field in current_entity.fields", v-text="$t('dashboard.'+field.text)")
                                                th >>
                                        tbody
                                            tr(v-if="current_entity.loading")
                                                td(v-bind:colspan="current_entity.fields.length + 1" style="text-align:center")
                                                    div.loader.sm-loader
                                            tr(v-else v-for="(row, index) in $store.getters['stage/full_entity_rows'](current_entity.service_state_id)")
                                                td {{ index + 1 }}
                                                template(v-for="(td,i) in row" v-if="i != 0")
                                                    td(v-if="master_sum_index == null || i != master_sum_index + 1" v-text="td")
                                                    td(v-else v-text="$root.format_money(td)")
                                                td
                                                    button.btn.btn-diga(v-on:click="show_client_page(row[0])") >>
                            div.modal-footer
                                div(style="float: left;")
                                    span(v-text="$t('dashboard.quantity') + ': '")
                                    span(v-text="current_entity.total_rows_count")
                                button.btn.btn-default(type="button" data-dismiss="modal") {{ $t('dashboard.close') }}
            template(v-if="$root.user.show_calendar_on_main_page === true")
                calendar(style="margin-bottom: 20px;")
            template(v-if="$root.module_enabled('analytics') == false")
                widgets-header
                section.stage-widgets
                    //- div.row
                    draggable.row(v-model="widgets")
                        stage-widget(v-for="widget in widgets", :key="widget.id", :widget="widget", v-on:popup_table="popup_table", v-on:popup_line="popup_line")
                div.modal.fade#modal-full-list-table(tabindex="-1" role="dialog")
                    div.modal-dialog
                        div.modal-content(v-if="popup_rows != null")
                            div.modal-header
                                h4.modal-title {{ popup_header }}
                                button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                                    span(aria-hidden="true") &times;
                            div.modal-body
                                div.form-horizontal(role="form")
                                    table-table(:rows="popup_rows", :colors="popup_colors")
                            div.modal-footer
                                div(style="float: left;")
                                    span(v-text="$t('dashboard.quantity') + ': '")
                                    span(v-text="popup_rows.length")
                                button.btn.btn-default(type="button" data-dismiss="modal") {{ $t('dashboard.close') }}
                div.modal.fade#modal-full-list-line(tabindex="-1" role="dialog")
                    div.modal-dialog
                        div.modal-content(v-if="popup_rows_for_line != null")
                            div.modal-header
                                h4.modal-title {{ popup_header_for_line }}
                                button(type="button" class="close" data-dismiss="modal" aria-label="Close")
                                    span(aria-hidden="true") &times;
                            div.modal-body
                                div.form-horizontal(role="form")
                                    table.table.table-bordered.table-striped
                                        thead
                                            tr
                                                td #
                                                td {{ $t('dashboard.master_number') }}
                                                td {{ $t('dashboard.master_sum') }}
                                                td >>
                                        tbody
                                            tr(v-for="(row, index) in popup_rows_for_line")
                                                td {{ index + 1 }}
                                                td {{ row.estimate_number }}
                                                td {{ row.estimate_summ }}
                                                td
                                                    button.btn.btn-diga(v-on:click="open_client_page_through_line(row.client_contact_id)") >>
                            div.modal-footer
                                div(style="float: left;")
                                    span(v-text="$t('dashboard.quantity') + ': '")
                                    span(v-text="popup_rows_for_line.length")
                                button.btn.btn-default(type="button" data-dismiss="modal") {{ $t('dashboard.close') }}
</template>

<script>

import moment from 'moment';
import stageStore from './../../vuex/modules/stage.js';
import entitiesHeader from './entities_header.vue';
import widgetsHeader from './widgets_header.vue';
import stageEntity from './entity.stage.vue';
import stageWidget from './widget.stage.vue';
import tableTable from './charts/table-table.stage.vue';
import draggable from 'vuedraggable'
import calendar from './../../../../../../Calendar/resources/assets/js/components/calendar/index.vue';


export default {
    components: {
        entitiesHeader, widgetsHeader, stageWidget, stageEntity, tableTable, draggable, calendar
    },
    data() {
        return {
            x: 0,
            animated_x: 0,
            scroll_amount: 400,
            loading: false,
            current_entity: null,
            popup_rows: null,
            popup_header: null,
            popup_colors: null,
            popup_rows_for_line: null,
            popup_header_for_line: null,
            config: null,
            array_widgets: [],
        }
    },
    methods: {
        // load_dashboard(){
        //     this.$root.global_loading = true;
        //     this.$http.get('/api/dashboards/' + this.$root.user.dashboard_id).then(res => {
        //         if (res.data.errcode == 1) {
        //             this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
        //         } else {
        //             this.widgets = res.data.widgets;
        //         }
        //         this.$root.global_loading = false;
        //     }, res => {
        //         this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
        //         this.$root.global_loading = false;
        //     });
        // },
        show_client_page(client_id){
            $('#modal-full-list').modal('hide');
            this.$router.push({name: 'client_show', params: {id: client_id}});
        },
        open_client_page_through_line(client_id){
            $('#modal-full-list-line').modal('hide');
            this.$router.push({name: 'client_show', params: {id: client_id}});
        },
        load_config(){
            this.$root.global_loading = true;
            return new Promise((resolve, reject) => {
                this.$http.get('/api/dashboards/config').then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                        reject();
                    } else {
                        this.config = res.data;
                        resolve();
                    }
                    this.$root.global_loading = false;
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                    this.$root.global_loading = false;
                    reject();
                });
            });
        },
        to_left: function(){
            if (this.x <= -1 * this.scroll_amount) {
                this.x += this.scroll_amount;
            } else {
                this.x = 0;
            }
        },
        to_right: function(){
            if (this.x - this.scroll_amount > -1 * (this.$refs.line.clientWidth - this.$refs.line_wrapper.clientWidth)) {
                this.x -= this.scroll_amount;
            } else {
                this.x = -1 * (this.$refs.line.clientWidth - this.$refs.line_wrapper.clientWidth);
            }
        },
        full: function(entity){
            this.current_entity = entity;
            $('#modal-full-list').modal('show');
        },
        popup_table(rows, header, colors){
            this.popup_rows = rows;
            this.popup_header = header;
            this.popup_colors = colors;
            $('#modal-full-list-table').modal('show');
        },
        popup_line(rows, header){
            let $this = this;
            this.popup_rows_for_line = rows;
            this.popup_header_for_line = header;
            $('#modal-full-list-line').on('shown.bs.modal', function (e) {
                $this.$root.global_loading = false;
            });
            $('#modal-full-list-line').modal('show');
        },
    },
    created() {
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('dashboard.Dashboard');

        if (this.$root.module_enabled('analytics') == false){
            this.$cookie.delete('calendar-user');
        }        


        this.loading = true;

        //this.$store.registerModule('stage', stageStore);
        // this.load_dashboard();

        this.load_config().then(() => {
            let data = {
                users: this.$store.getters.getUsersById,
                filters: {
                    range: {
                        start: {
                            date: moment().subtract(1, 'month').format(),
                        },
                        end: {
                            date: moment().format(),
                        },
                    },
                    responsible: this.$root.user.id,
                    use_range: null,
                    w_range: {
                        start: {
                            date: moment().subtract(1, 'month').format(),
                        },
                        end: {
                            date: moment().format(),
                        },
                    },
                    w_use_range: null,
                },
                entity_field_types: this.config.entity_field_types,
            };
            this.$store.dispatch('stage/init', data).then(() => {
                this.$store.dispatch('stage/get_dashboard').then(() => {
                    this.loading = false;
                })
            });

            this.$root.global_loading = false;
        });

    },
    beforeDestroy(){
        // if (this.$root.user.dashboard_id !== 0 && this.$root.user.dashboard_id !== null){
        //     console.log('ssss');
        //     this.$store.unregisterModule('stage');
        // }
    },
    watch: {
        x: function(newValue, oldValue){
            var $this = this;
            function animate () {
                if (TWEEN.update()) {
                    requestAnimationFrame(animate);
                }
            }
            new TWEEN.Tween({ tmp_x: oldValue })
                .easing(TWEEN.Easing.Quadratic.Out)
                .to({ tmp_x: newValue }, 500)
                .onUpdate(function () {
                    $this.animated_x = this.tmp_x.toFixed(0);
                })
                .start();
            animate();
        },
    },
    computed: {
        master_sum_index(){
            return this.$store.getters['stage/master_sum_index'](this.current_entity.service_state_id)
        },
        ordered_entities() {
            return Object.values(this.$store.getters['stage/entities']).sort(function(a, b) {
                if (a.state.order > b.state.order){
                    return 1;
                } else if (b.state.order > a.state.order){
                    return -1;
                } else {
                    return 0;
                }
            });
        },
        widgets: {
            get() {
                var widgets_list = Object.values(this.$store.getters['stage/widgets']);
                if (widgets_list && widgets_list.length > 0){
                    var user_order = JSON.parse(this.$root.user.widget_order);
                    if (user_order && user_order.length > 0){
                        widgets_list.sort(function(a, b){  
                            return user_order.indexOf(a.id) - user_order.indexOf(b.id);
                        });
                    }
                }
                return widgets_list;
            },
            set(value) {
                var ids = value.map(function(n, i){
                    return n.id;
                });
                this.$http.post('/api/hr/' + this.$root.user.id + '/dashboard_widgets', {dashboard_widget_ids : ids}).then(res => {
                    if (res.data.errcode == 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        this.$toastr.s(this.$root.$t("template.Settings_saved"), this.$root.$t("template.Success"));
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
                this.$root.user.widget_order = JSON.stringify(ids);
                // this.$store.commit('update_widget', value);
            }                         
        },
    },
}

</script>