<style>

</style>

<template lang="pug">
    div.mb-2
        div.project-section {{ $t('project.Equipment_by_specifications') }}
        div.mb-2(v-for="(spec,i) in project.specifications")
            .row
                .col-12.col-md-3
                    fieldset.form-group
                        label {{ $t('project.Specification') }}
                        div.d-flex
                            v-select.w-100.mr-2(v-if="spec.from_bd"
                                v-model='tmp_specification',
                                label="name",
                                :debounce='250',
                                :on-change='(val) => { spec_select(val, spec)}',
                                :on-search='get_spec_options',
                                :options='curr_spec_list',
                                v-bind:placeholder="$t('estimate.Escolha_a_opcao')",
                                :disabled="project.finished || !$root.can_with_project('update', 3)")
                                template(slot="no-options") {{ $t('template.No_matching_options') }}
                            input.form-control.mr-2(v-else v-model="spec.name", :disabled="project.finished || !$root.can_with_project('update', 3)")
                            button.btn.btn-diga(v-on:click="from_bd_toggle(spec)", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ spec.from_bd ? $t('project.Enter_manually') : $t('project.Choose_from_db') }}
                    fieldset.form-group
                        label {{ $t('project.Notes') }}
                        textarea.form-control(v-model="spec.notes", :disabled="project.finished || !$root.can_with_project('update', 3)")
                    button.btn.btn-danger(v-on:click="remove_spec(spec)", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('project.Remove_specification') }}
                .col-12.col-md-9
                    equipment-table(:spec="spec", :selectable="true", :disabled="project.finished || !$root.can_with_project('update', 3)")
        button.btn.btn-diga(v-on:click="add_spec", :disabled="project.finished || !$root.can_with_project('update', 3)") {{ $t('project.Add_specification') }}
</template>

<script>
import equipment_table from './../../shared/equipment_table.vue'

export default {
    inject: ['$validator'],
    components: {
        'equipment-table': equipment_table,
    },
    props: ['project'],
    data: function() {
        return {
            curr_spec_list: [],
            tmp_specification: null,
        }
    },
    created(){
        if (this.project.specifications.length == 0){ // if it is creating of new project, add prepayment field
            this.add_spec();
        }
    },
    methods: {
        from_bd_toggle(spec){
            if (!spec.from_bd){
                if (spec.name != '' || spec.notes != '' || spec.equipment.length != 0){
                    if (confirm(this.$root.$t("project.Sure_from_bd"))) {
                        this.erase_spec(spec);
                        this.tmp_specification = null;
                        spec.from_bd = true;
                    }
                } else {
                    this.erase_spec(spec);
                    this.tmp_specification = null;
                    spec.from_bd = true;
                }
            } else {
                spec.from_bd = false;
            }
        },
        erase_spec(spec){
            spec.name = '';
            spec.notes = '';
            if (!('removed_equipment' in spec)){
                spec.removed_equipment = [];
            }
            spec.removed_equipment = spec.removed_equipment.concat(spec.equipment.map(i => i.id));
            spec.equipment = [];
        },
        add_spec(){
            let newSpecification = {
                id: 0,
                name: '',
                equipment: [],
                notes: '',
                from_bd: false,
            };
            this.project.specifications.push(newSpecification);
        },
        remove_spec(spec){
            if (confirm(this.$root.$t("project.Sure_remove_specification"))){
                if (!('removed_specifications' in this.project)){
                    this.project.removed_specifications = [];
                }
                this.project.removed_specifications.push(spec.id);
                let index = this.project.specifications.indexOf(spec);
                this.project.specifications.splice(index, 1);
            }
        },
        get_spec_options(search, loading) {
            loading(true);
            this.$http.get('/api/specifications?limit=9999&search=' + search).then(res => {
                this.curr_spec_list = res.data.rows;
                loading(false);
            }, res => {
                this.$toastr.e(this.$root.$t("template.Something_bad_happened"), this.$root.$t("template.Error"));
                loading(false);
            })
        },
        spec_select(val, item){
            if (val !== null && item != val) {
                let copy = JSON.parse(JSON.stringify(val));

                copy.id = item.id;
                copy.equipment.forEach(i => {
                    i.id = 0;
                });
                copy.from_bd = false;

                console.log(copy);

                Object.assign(item, copy);
            }
            console.log('eeee');
        },
    },
}
</script>
