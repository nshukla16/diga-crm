<style>

</style>

<template lang="pug">
    div.notifications(v-on-clickaway="$root.hide_notifications", :style="{left: x+'px'}" v-cloak)
        div.not-top
            span(v-text="$t('template.Notifications')")
            a(style="float: right;" href="#" v-text="$t('template.Mark_all_read')", @click="$root.mark_all_as_read")
        div.not-wrapper
            div(v-for="notification in $root.notifications", :class="{read: notification.read, notif: true}")
                i(:title="notification.read ? $t('template.Mark_as_unread') : $t('template.Mark_as_read')", :class="{clickable: true, 'mr-1': true, fa: true, 'fa-square-o': !notification.read, 'fa-check-square-o': notification.read}", @click="$root.mark_as_read(notification)")
                span(v-html="$root.notificationLeftPart(notification)")
                div(style="text-align: right;")
                    timeago.not-date(:datetime="notification.created_at", :auto-update="60", :title="notification.created_at")
            div(style="padding: 10px;text-align: center;" v-if="$root.notifications.length == 0" v-text="$t('template.Here_will_be_notifications')")
        div.not-bottom
            router-link.btn.btn-diga(style="width: 100%;", :to="{name: 'notifications'}" v-text="$t('template.See_all')")
</template>

<script>
export default {
    data(){
        return {
            x: 0,
            y: 0,
        }
    },
    mounted(){
        this.$nextTick(function() {
            window.addEventListener('resize', this.getWindowWidth);
        });

        this.getWindowWidth();
    },
    methods: {
        getWindowWidth(event) {
            let windowWidth = document.documentElement.clientWidth;
            let not_x = $('.notification-button')[0].getBoundingClientRect().left;
            let space_after_not = windowWidth - not_x;
            this.x = not_x;
            if (space_after_not < 400){
                this.x -= 400 - space_after_not;
            }
        },
    },
    beforeDestroy() {
        window.removeEventListener('resize', this.getWindowWidth);
    },
}
</script>