<style>

</style>

<template lang="pug">
    div.mb-2
        template(v-if="manufacturer_opened")
            button.btn.btn-diga.w-100(v-if="project.manufacturers.length > 1" v-on:click="back_to_list") << {{ $t('project.Back_to_list') }}
            div.project-section(v-if="$root.can_with_project('read' , 0)") {{ $t('project.Manufacturer_relationships') + ': ' + manufacturer_opened.manufacturer.name }}
            manufacturer_common(v-if="$root.can_with_project('read', 0)", :project="project", :manufacturer="manufacturer_opened")
            template(v-if="$root.can_with_project('read', 1)")
                manufacturer_contract_specification(v-if="project.contract_type == 0", :project="project", :manufacturer="manufacturer_opened")
                manufacturer_inner_steps(v-if="project.contract_type == 0", :project="project", :manufacturer="manufacturer_opened")
                manufacturer_payment_steps(v-if="project.contract_type == 0", :project="project", :manufacturer="manufacturer_opened")
                div.mb-2(v-if="project.contract_type == 1")
                    div(v-for="commission_relation in manufacturer_opened.commission_relations")
                        manufacturer_commission_steps(:project="project", :manufacturer="manufacturer_opened" :relation="commission_relation")
                manufacturer_preparation_steps(:project="project", :manufacturer="manufacturer_opened")
        template(v-else)
            div.project-section {{ $t('project.Manufacturer_relationships') }}
            table.table
                thead
                    tr
                        th {{ $t('project.Manufacturer') }}
                        th {{ $t('project.Actions') }}
                tbody
                    tr(v-for="(manufacturer,i) in project.manufacturers")
                        td {{ manufacturer.manufacturer.name }}
                        td
                            button.btn.btn-diga(v-on:click="open(i)") {{ $t('template.Read_more') }}
</template>

<script>
import manufacturer_preparation_steps from './manufacturer_preparation_steps.vue';
import manufacturer_inner_steps from './manufacturer_inner_steps.vue';
import manufacturer_payment_steps from './manufacturer_payment_steps.vue';
import manufacturer_common from './manufacturer_common.vue';
import manufacturer_contract_specification from './manufacturer_contract_specification.vue';
import manufacturer_commission_steps from './manufacturer_commission_steps.vue';
import {mapGetters} from "vuex";

export default {
    components: {
        manufacturer_preparation_steps, manufacturer_payment_steps, manufacturer_inner_steps, manufacturer_commission_steps, manufacturer_common, manufacturer_contract_specification,
    },
    props: {
        project: {
            type: Object,
        },
    },
    data: function () {
        return {
            opened_index: null,
        }
    },
    computed: {
        ...mapGetters({
            legal_entities: 'getLegalEntities',
        }),
        manufacturer_opened(){
            if (this.opened_index !== null){
                return this.project.manufacturers[this.opened_index];
            } else {
                return null;
            }
        },
    },
    mounted(){
        if (this.project.manufacturers.length === 1){
            this.open(0);
        }
    },
    methods: {
        back_to_list(){
            this.opened_index = null;
        },
        open(i){
            this.opened_index = i;
        },
    },
}
</script>