<template lang="pug">
    div {{ dateFormat(row.created_at) }}
</template>

<script>

import moment from 'moment';

export default {
    props: ['row'],
    methods: {
        dateFormat(datetime) {
            return moment(datetime).format('MMM Do YYYY');
        },
    },
}
</script>