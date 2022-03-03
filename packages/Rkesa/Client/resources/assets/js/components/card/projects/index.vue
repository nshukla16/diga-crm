<style>
    #projects-list{

    }
</style>

<template lang="pug">
    div
        #projects_block.diga-container(style="height: 550px;")
            .portlet-title
                .caption
                    router-link.btn(:to="{ name: 'project_create' }")
                        i.fa.fa-plus
                    card-menu(:current_section="current_section")
            .portlet-body
                div(v-bar="" style="height: 480px;")
                    div#projects-scroller
                        div#projects-list.pt-2.pl-2(v-if="projects.length > 0")
                            div.mb-2(v-for="project in projects")
                                div(style="width: 80%;display: inline-block;vertical-align: middle;")
                                    router-link(:to="{ name: 'project_show', params: {id: project.id} }") {{ project.name }}
                                div.date(style="width: 20%;display: inline-block;vertical-align: middle;") {{ project.created_at }}
                        div.empty-filler(v-else) {{ $t("client.There_is_no_projects") }}
</template>

<script>

import CardMenu from '../card_menu.vue';

export default {
    props: ['projects', 'current_section'],
    components: {
        CardMenu
    },
    data: function () {
        return {
        }
    },
    methods: {

    },

}
</script>