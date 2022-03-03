<template lang="pug">
div
    div
        section.diga-container(style="height: 550px;")
            .portlet-title
                .caption(style="padding-right: 10px;")
                    router-link.btn(:to="{name: 'equipment_create'}")
                        i.fa.fa-plus                       
                    card-menu(:current_section="current_section")                
            .portlet-body
                div(v-bar="" style="height: 480px;")
                    div
                        div(v-if="manufacturer.equipment.length > 0")
                            .item.with-gradient(v-for="equipment in manufacturer.equipment")
                                div
                                    b(style="font-size: 20px;") {{ equipment.name }}
                                    div.float-right
                                        router-link(:to="{name: 'equipment_edit', params: {id: equipment.id}}")
                                            i.fa.fa-pencil
                                div
                                    b.mr-2 {{ $t('project.Measure') }}
                                    | {{ unitsById[equipment.estimate_unit_id].measure }}
                                div
                                    b.mr-2 {{ $t("project.Size") }}
                                    | {{ equipment.size }}
                                div
                                    b.mr-2 {{ $t("project.Model") }}
                                    | {{ equipment.model }}
                                div
                                    b.mr-2 {{ $t("project.Vendor_code") }}:
                                    | {{ equipment.vendor_code }}
                        div.empty-filler(v-else) {{ $t('project.No_equipments') }}

</template>

<script>
import {mapGetters} from "vuex";
import cardMenu from '../card_menu.vue';

export default {
    props: ['manufacturer','current_section'],
    components: {
        cardMenu
    },
    data: function () {
        return {
        }
    },
    methods: {

    },
    computed:{
        ...mapGetters({
            unitsById: 'getEstimateUnitsById',
        }),
    }

}
</script>