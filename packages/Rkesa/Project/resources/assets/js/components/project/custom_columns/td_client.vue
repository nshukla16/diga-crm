<template lang="pug">
    router-link(:to="{name: 'company_show', params: {id: row.client_id}}") {{ row.client.name }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: ['row'],
    computed: {

    },
}
</script>