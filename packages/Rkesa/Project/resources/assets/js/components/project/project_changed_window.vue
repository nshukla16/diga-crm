<template lang="pug">
    div.modal.fade#projectChangedModal(tabindex="-1" role="dialog" aria-hidden="true")
        div.modal-dialog(role="document")
            div.modal-content(v-if="user_id")
                div.modal-body
                    div.text-center {{ $t('project.Please_reload_project', {user: users_by_id[user_id].name}) }}
                    div.text-center
                        button.btn.btn-diga(v-on:click="reload") {{ $t('project.Reload') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['project_id'],
    data: function(){
        return {
            user_id: null,
        }
    },
    computed: {
        ...mapGetters({
            users_by_id: 'getUsersById',
        }),
    },
    methods: {
        show(project_id, user_id){
            if (this.project_id == project_id && this.$root.user.id != user_id){
                this.user_id = user_id;
                jQuery('#projectChangedModal').modal('show');
            }
        },
        reload(){
            window.location.reload(false);
        },
    },
    mounted(){
        this.$bus.$on("project_changed_event", this.show);
    },
    beforeDestroy: function() {
        this.show && this.$bus.$off("project_changed_event", this.show);
    },
}
</script>