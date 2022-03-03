<template lang="pug">
    router-link(:to="{name: 'project_show', params: {id: row.id}}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
    computed: {

    },
}
</script>