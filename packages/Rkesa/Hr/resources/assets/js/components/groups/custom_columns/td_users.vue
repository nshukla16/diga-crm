<template lang="pug">
    div
        span {{ row.users_ids.map(id => userById[id].name).join(', ') }}
</template>

<script>
import {mapGetters} from "vuex";

export default {
    props: ['row'],
    computed: {
        ...mapGetters({
            userById: 'getUsersById',
        })
    }
}
</script>