<template lang="pug">
    router-link(:to="{ name: 'legal_entity_show', params: {id: row.id }}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
    computed: {

    },
}
</script>