<template lang="pug">
    router-link(v-if="row.manufacturer_id" :to="{name: 'manufacturer_show', params: {id: row.manufacturer_id}}") {{ row.manufacturer.name }}
</template>

<script>
import { mapGetters } from 'vuex';

export default {
    props: ['row'],
    computed: {

    },
}
</script>