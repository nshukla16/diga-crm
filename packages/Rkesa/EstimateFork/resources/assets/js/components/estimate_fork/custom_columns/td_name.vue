<template lang="pug">
    router-link(:to="{name: 'estimate_fork_edit', params: {id: row.id}}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
    methods: {

    },
}
</script>