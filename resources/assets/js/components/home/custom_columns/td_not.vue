<template lang="pug">
    div(:class="{read: row.read, notif: true}")
        timeago.not-date.ml-2.float-right(:datetime="row.created_at", :auto-update="60", :title="row.created_at")
        span.float-right(v-html="$root.notificationRightPart(row)")
        i(:title="row.read ? $t('template.Mark_as_unread') : $t('template.Mark_as_read')", :class="{'mr-1': true, clickable: true, fa: true, 'fa-square-o': !row.read, 'fa-check-square-o': row.read}", @click="$root.mark_as_read(row)")
        span(v-html="$root.notificationLeftPart(row)")
</template>

<script>
export default {
    props: ['row'],
    methods: {

    },
}
</script>