<style>
    .form-manage{
        width: 20%;
    }
    .control-label{
        width: 100%;
    }
    .vc-sketch {
         left: 0px;
    }
</style>

<template lang="pug">
    div
        //div.row
        //    div.col-12.col-md-6.mb-3
        div.diga-container.p-4
            h2 {{ $t('template.PlanningSettings') }}
            div.row
                div.col-lg-3.col-sm-12
                    fieldset.form-group
                        label.control-label {{ $t('template.BeginningOfWorkingHours') }}
                            input.form-control(v-model="startHours" type="number" min="0" max="23")
                    fieldset.form-group
                        label.control-label {{ $t('template.EndingOfWorkingHours') }}
                            input.form-control(v-model="finishHours" type="number" min="0" max="23")
                    fieldset.form-group
                        label.control-label {{ $t('template.Accountant') }}
                        select.form-control(v-model="accountant_id")
                            option(v-for="user in users", :value="user.id") {{ user.name }}
                    fieldset.form-group
                        label.control-label {{ $t('template.Construction_manager_id') }}
                        select.form-control(v-model="construction_manager_id")
                            option(v-for="user in users", :value="user.id") {{ user.name }}
                    fieldset.form-group
                        label.control-label {{ $t('template.Default_roadmap_task_color') }}
                        div.position-relative
                            sketch-picker(v-if="color_picker_show" v-model="default_task_color" v-on-clickaway="hide_picker")
                            div.color-icon.color(v-bind:style="{'background-color': default_task_color.hex}" v-on:click="color_picker_show = !color_picker_show")
                            input.form-control.settings-inputs(style="width: 100px;" v-model="default_task_color.hex")
                    fieldset.form-group
                        label.control-label {{ $t('template.Make_auto_calculation') }}
                            input.ml-2(type="checkbox" v-model="auto_calculation" true-value="1" false-value="0")
                    fieldset.form-group
                        label.typo__label {{$t('template.select_responsibles_for_tasks')}}
                        multiselect(
                        v-model="list_of_managers",
                        :options="users",
                        :multiple="true",
                        :close-on-select="false",
                        :clear-on-select="false",
                        :preserve-search="true",
                        :placeholder="$t('dashboard.select_participating_states')"
                        label="name",
                        track-by="name",
                        :selectLabel="$t('dashboard.press_enter_to_select')",
                        :deselectLabel="$t('dashboard.press_enter_to_remove')",
                        :selectedLabel="$t('dashboard.option_selected')",
                        )

        div.mt-3
            button.btn.btn-diga(v-on:click="save_settings") {{ $t('template.Save') }}

</template>

<script>

import {mapGetters} from "vuex";

export default {
    data() {
        return {
            startHours: '',
            finishHours: '',
            accountant_id: '',
            default_task_color: [],
            color_picker_show: false,
            auto_calculation: '',
            construction_manager_id: '',
            list_of_managers: [],
            options: [],
            // users: null,
        }
    },
    mounted(){
        // console.log(this.$root.settings);
        this.startHours = this.$root.settings.planning_working_hours_start;
        this.finishHours = this.$root.settings.planning_working_hours_end;
        this.accountant_id = this.$root.settings.accountant_user_id;
        this.auto_calculation = this.$root.settings.make_auto_calculation_for_payment_steps;
        this.default_task_color.hex = this.$root.settings.default_color_of_roadmap_task;
        this.construction_manager_id = this.$root.settings.construction_manager;
        var manager_list_to_obj = JSON.parse(this.$root.settings.construction_manager_list);
        console.log(manager_list_to_obj);
        manager_list_to_obj.map(m => {
            var manager = this.users_by_id[m.id];
            this.list_of_managers.push(manager);
        });

    },
    computed: {
        ...mapGetters({
            users: 'getUsers',
            users_by_id: 'getUsersById',
        }),
    },
    methods: {
        save_settings() {
            var filtered_managers = this.list_of_managers.map(function (user) {
                return {"id": user.id};
            });
            filtered_managers = JSON.stringify(filtered_managers);
            console.log(filtered_managers);
            this.$root.global_loading = true;
            this.$http.post('/api/settings', {
                planning_working_hours_start: this.startHours,
                planning_working_hours_end: this.finishHours,
                accountant_user_id: this.accountant_id,
                default_color_of_roadmap_task: this.default_task_color.hex,
                make_auto_calculation_for_payment_steps: this.auto_calculation,
                construction_manager: this.construction_manager_id,
                construction_manager_list: filtered_managers,
            }).then(res => {
                if (res.data.errcode == 1) {
                    this.$toastr.e(res.data.errmess, this.$root.$t("template.Error"));
                } else {
                    this.$toastr.s(this.$root.$t("client.Settings_saved"), this.$root.$t("template.Success"));
                }
                this.$root.global_loading = false;
            }, res => {
                this.$toastr.e(this.$root.$t("template.Server_error"), this.$root.$t("template.Error"));
                this.$root.global_loading = false;
            });
        },
        // show_picker(){
        //     this.color_picker_show = !this.color_picker_show;
        // }
        hide_picker(){
            this.color_picker_show = false;
        },
    },
}
</script>