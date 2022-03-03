<template lang="pug">
    div
        h2 {{ $t('template.Estimate_planning') }}
        section.diga-container.p-4
            div.float-sm-right.mr-2
                button.btn.btn-diga(style="height:38px;", v-on:click="new_plan()") {{ $t('gantt.Plan_new') }}
            datatable.datatable-wrapper.companies-wrapper(v-bind="table")
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
                            label {{ $t('gantt.Type') }}
                            select.form-control(v-model="estimate_planning_type")
                                option(v-if="$root.module_enabled('estimate')" :value="0") {{ $t("gantt.Estimate") }}
                                option(:value="1") {{ $t("gantt.Custom") }}

                        fieldset.form-group(v-if="estimate_planning_type == 0", :class="{ 'has-error': errors.has('selected_estimate') }")
                            v-select(
                                :debounce='250',
                                :on-search='get_estimates_options',
                                :on-change='estimate_select',
                                v-model="selected_estimate",
                                :options='tmp_estimates',
                                :placeholder="$t('gantt.Choose_estimate')",
                                name="selected_estimate",
                                v-validate="'required'",
                                v-bind:data-vv-as="$t('gantt.Estimate').toLowerCase()")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                            span.help-block(v-show="errors.has('selected_estimate')") {{ errors.first('selected_estimate') }}

                        fieldset.form-group(v-if="estimate_planning_type == 0")
                            label {{ $t('project.Initial_date') }}
                            date-picker.form-group(style="margin-bottom:15px; margin-left: 10px", format="DD.MM.YYYY", v-model="estimate_date_start", :value-type="$root.valueType", :lang="$root.locale", :first-day-of-week="$root.global_settings.first_day_of_week", :width="'30%'")
                        
                        div.text-center.mt-3
                            button.btn.btn-diga(v-on:click="create_plan") {{ $t('template.Create') }}
</template>

<script>
import name_column from './custom_columns/td_name.vue';
import estimate_column from './custom_columns/td_estimate_number.vue';
import action_column from './custom_columns/td_estimate_action.vue';
import estimate_type from './custom_columns/td_estimate_type.vue';
import moment from 'moment';

export default {
    props: ['offset'],
    data(){
        return {
            table: {
                columns: [
                    { title: '#', field: 'id', sortable: true, tdClass: 'column_autosize' },
                    { title: this.$root.$t('gantt.Planning_name'), field: 'name', tdComp: name_column, sortable: true },
                    { title: this.$root.$t('gantt.Estimate_number'), tdComp: estimate_column, visible: this.$root.module_enabled('estimate') },
                    { title: this.$root.$t('gantt.Type'), tdComp: estimate_type },
                    { title: this.$root.$t('template.Actions'), tdComp: action_column, tdClass: 'column_autosize' },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            selected_estimate: null,
            estimate_name: '',
            tmp_estimates: [],
            estimate_planning_type: 0,
            estimate_date_start: null,
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.Estimate_planning');
        this.estimate_date_start = moment().add(1, 'days');
        if (this.estimate_date_start.weekday() === 7){
            this.estimate_date_start = moment().add(2, 'days');
        }
        
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        create_plan(){
			this.$root.global_loading = true;
            this.$validator.validate().then(result => {
                if (!result) {
                    this.$toastr.w(this.$t("template.Need_to_fill"), this.$t("template.Warning"));
                    return;
                }
				let temp;
	            if (this.estimate_planning_type === 0){
	                temp = this.selected_estimate.value;
	            } else {
	                temp = null;
	            }

	            this.$http.post('/api/estimate_plannings', {
	                estimate_id: temp,
	                name: this.estimate_name,
                    is_custom: this.estimate_planning_type,
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
        new_plan(){
            this.selected_estimate = null;
            this.estimate_planning_type = 1;
            jQuery('#newPlanModal').modal('show');
        },
        cancel_new_plan(){
            jQuery('#newPlanModal').modal('hide');
        },
        get_estimates_options(search, loading) {
            loading(true);
            this.$http.get('/api/estimates?limit=20&query=' + search).then(res => {
                var processedData = [];
                let $this = this;
                res.data.rows.forEach(function(i){
                    processedData.push({'label': $this.$root.estimate_number(i), 'value': i.id});
                });
                this.tmp_estimates = processedData;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
            })
        },
        estimate_select(res){
            this.selected_estimate = res;
        },
        //
        getResults() {
            this.$http.get('/api/estimate_plannings?' + this.$root.params(this.table.query)).then(res => {
                return res.json();
            }).then(data => {
                if (data.errcode == 1) {
                    this.$toastr.e(data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.table.data = data.rows;
                    this.table.total = data.total;
                }
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
            });
        },
    },
    watch: {
        'table.query': {
            handler(query) {
                this.getResults();
            },
            deep: true,
        },
    },
}
</script>