<template lang="pug">
    div {{ usersById[row.user_id].name }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    computed: {
        ...mapGetters({
            usersById: 'getUsersById',
        }),
    },
    methods: {
    },
}
</script>