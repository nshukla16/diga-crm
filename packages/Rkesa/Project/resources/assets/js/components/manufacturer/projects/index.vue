<template lang="pug">
div
    div
        section.diga-container(style="height: 550px;")
            .portlet-title
                .caption(style="padding-right: 10px;")
                    card-menu(:current_section="current_section")    
            .portlet-body
                div(v-bar="" style="height: 480px;")
                    div
                        div(v-if="manufacturer.projects_manufacturers.length > 0")
                            .item.with-gradient(v-for="(pm, index) in manufacturer.projects_manufacturers")
                                span.clickable.clickable-link
                                    a(:href="'/projects/' +  pm.project.id" target="_blank") {{ (index + 1) + '. ' + pm.project.name }}
                        div.empty-filler(v-else) {{ $t('project.Not_set') }}

</template>

<script>

import cardMenu from '../card_menu.vue';

export default {
    props: ['manufacturer', 'current_section'],
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
    }

}
</script>