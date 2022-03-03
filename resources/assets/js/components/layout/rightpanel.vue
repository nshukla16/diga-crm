<style>
    .rightpanel{
        position: fixed;
        right: 0;
        top: 0;
        width: 60px;
        height: 100%;
        background-color: #eee;
        z-index: 1;
    }
    .rightpanel-toggler{
        width: 40px;
        height: 40px;
        background-color: #ffffff;
        text-align: center;
        line-height: 40px;
        position: fixed;
        right: 35px;
        bottom: 10px;
        z-index: 2;
        border: 1px solid #d4d4d4;
        border-radius: 50%;
        cursor: pointer;
    }
    .panel-element{
        height: 40px;
        width: 60px;
        padding: 5px;
        text-align: center;
        position: relative;
    }
    .panel-element:hover{
        background-color: #eee3c9;
    }
    .panel-element img{
        height: 30px;
        width: 30px;
    }
    .with-popup{
        /*margin-right: 15px;*/
    }
    .panel-not-read-badge{
        background-color: #d87272;
        color: white;
        border-radius: 10px;
        padding: 0 6px;
        font-size: 12px;
        margin-top: 6px;
        position: absolute;
        right: 5px;
        bottom: 0;
    }
    .modal-chat > div{
        width: 100%;
        max-width: 100%;
        height: 100%;
        min-height: 100%;
    }
    .modal-chat > div > div{
        height: 100%;
    }
    .panel-chat-status{
        height: 10px;
        width: 10px;
        display: block;
        position: absolute;
        top: 28px;
        left: 15px;
    }
</style>

<template lang="pug">
    div
        div.rightpanel-toggler
            i.fa(:class="{'fa-chevron-right': rightpanel_visible, 'fa-chevron-left': !rightpanel_visible}" @click="toggle_rightpanel")
        transition(name="fade")
            div(v-show="rightpanel_visible" :class="{'rightpanel': true, 'with-popup': chat_opened}")
                div.panel-element.clickable(@click="open_chat")
                    i.fa.fa-comments(style="font-size: 24px;margin-top: 2px;")
                div.panel-element.clickable(v-for="dialog in dialogs", @click="open_chat(dialog)")
                    img.img-circle(:src="get_dialog_photo(dialog)")
                    span.panel-chat-status.img-circle(:style="{'background-color': (is_online(dialog) ? 'rgb(29, 181, 70)' : 'rgb(212, 14, 14)')}")
                    span.panel-not-read-badge(v-show="dialog.not_read_count !== 0") {{ dialog.not_read_count }}
        div.modal.fade.modal-chat(id="chatModal" tabindex="-1" aria-hidden="true" style="bottom:5% !important;")
            div.modal-dialog.modal-dialog-centered.modal(role="document")
                div.modal-content
                    div.modal-header(style="padding-top: 0px; padding-bottom: 0px;")
                        button.close(data-dismiss="modal" aria-label="Close") x                            
                    DigaChat(
                        ref="chat"
                        :online="$root.online"
                        :user="user"
                        :users="chat_users"
                        :users_by_id="chat_users_by_id"
                        :chats_index_url="'/api/chats'"
                        :chat_make_as_read_url="'/api/chat_messages/:id/make_as_read'"
                        :chat_send_message_url="'/api/chats/:id/messages'"
                        :chat_messages_url="'/api/chats/:id/messages'"
                        :chats_new_chat_url="'/api/chats'"
                        :can_create_new_chats="true",
                        :chat_send_files_url="'/api/chats/file_upload'"
                        )
</template>

<script>
import {mapGetters} from "vuex";
import 'diga-chat/dist/diga-chat.css'
import ChatHelper from 'diga-chat/src/chat-helper.js'

export default {
    data(){
        return {
            chat_opened: false,
            rightpanel_visible: true,
            window_width: null,
            chatUsers: []
        }
    },
    computed: {
        ...mapGetters({
            user: 'getUser',
            chat_users: 'getChatUsers',
            chat_users_by_id: 'getChatUsersById',
            dialogs: 'getDialogs',
        }),
    },
    mounted(){
        $('#chatModal').on('hide.bs.modal', this.close_chat);

        if (window.innerWidth < 991){
            this.rightpanel_visible = false;
        }

        if (window.innerWidth < 991){
            this.rightpanel_visible = false;
        }

        let $this = this;
        Echo.private(this.$root.pusher_user_channel_name)
            .listen('ChatMessageEvent', (e) => {
                if ($this.$store.getters.getActiveDialog && $this.$store.getters.getActiveDialog.id === e.message.chat_id){
                    $this.$store.commit('addMessageToActiveDialog', {...e.message, message: ChatHelper.format_message(e.message.message)});
                    $this.$bus.$emit('scrollToBottom');
                    $this.$store.dispatch('readMessage', e.message.id);
                } else {
                    $this.$store.commit('dialogAddNotRead', e.message.chat_id);
                    let dialog = $this.$store.getters.getDialogs.find(d => d.id === e.message.chat_id);
                    $this.$toastr.s(ChatHelper.format_message(e.message.message), $this.$refs.chat.get_dialog_name(dialog));
                }
            })
            .listen('ChatCreatedEvent', (e) => {
                $this.$store.commit('addDialog', e.chat);
            })
    },
    methods: {
        toggle_rightpanel(){
            this.rightpanel_visible = !this.rightpanel_visible;
        },
        get_dialog_photo(dialog){
            // we check for existance of reference to chat component because refs are being created after mounting
            // but get_dialog_photo() is being executed before mounted hook
            return this.$refs.chat ? this.$refs.chat.get_dialog_photo(dialog) : "";
        },
        is_online(dialog){
            return this.$refs.chat ? this.$refs.chat.is_online(dialog) : false;
        },
        open_chat(dialog = null){
            this.$bus.$emit('open_dialog', dialog);
            this.chat_opened = true;
            jQuery('#chatModal').modal('show');
            // We should scroll to the bottom here (BUT WE DONT)
        },
        close_chat(){
            this.chat_opened = false;
            this.$store.commit('setActiveDialog', null);
        },
    },
}
</script>