<template lang="pug">
    div
        h2 {{ $t('template.User_planning') }}
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
                            label {{ $t('gantt.Plan_name') }}
                            input.form-control.mr-2(v-model="task_name"  name="task_name", v-validate="'required'", v-bind:data-vv-as="$t('gantt.Plan_name').toLowerCase()")
                            h6.help-block(v-show="errors.has('task_name')") {{ errors.first('task_name') }}
                        fieldset.form-group
                            label {{ $t('gantt.Type') }}
                            select.form-control(v-model="user_planning_type")
                                option(:value="0") {{ $t("gantt.Users") }}
                                option(:value="1") {{ $t("gantt.Custom") }}
                                option(:value="2" v-if="$root.module_enabled('estimate')") {{ $t("gantt.Groups") }}
                        div.text-center.mt-3
                            button.btn.btn-diga(v-on:click="create_plan") {{ $t('template.Create') }}
</template>

<script>
import user_planning_type from './custom_columns/td_user_planning_type.vue';
import name_column from './custom_columns/td_name.vue';
import action from './custom_columns/td_action.vue';

export default {
    props: ['offset'],
    data(){
        return {
            table: {
                columns: [
                    { title: '#', tdStyle: 'width: 50px;', field: 'id', sortable: true },
                    { title: this.$root.$t('gantt.Planning_name'), field: 'name', tdComp: name_column, sortable: true },
                    { title: this.$root.$t('gantt.Type'), tdComp: user_planning_type },
                    { title: this.$root.$t('estimate.Actions'), tdComp: action, tdClass: 'column_autosize' },
                ],
                data: [],
                total: 0,
                query: {
                    offset: this.offset || 0,
                },
            },
            task_name: '',
            user_planning_type: 0,
        }
    },
    mounted(){
        document.title = this.$root.global_settings.site_name + ' | ' + this.$root.$t('template.User_planning');
        this.$bus.$on("get_results", this.getResults);
    },
    beforeDestroy: function() {
        this.getResults && this.$bus.$off("get_results", this.getResults);
    },
    methods: {
        create_plan(){
            // console.log(this.user_planning_type);
            this.$validator.validateAll().then(result => {
                if (!result) {
                    this.$toastr.w(this.$root.$t("template.Need_to_fill"), this.$root.$t("template.Warning"));
                    return;
                }
                this.$http.post('/api/user_plannings', {name: this.task_name, is_custom: this.user_planning_type}).then(res => {
                    if (res.data.errcode === 1) {
                        this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                    } else {
                        jQuery('#newPlanModal').modal('hide');
                        this.$router.push({name: 'user_planning_show', params: {id: res.data.id}});
                    }
                }, res => {
                    this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                });
            });
        },
        new_plan(){
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
            this.$http.get('/api/user_plannings?' + this.$root.params(this.table.query)).then(res => {
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