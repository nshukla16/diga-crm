<template lang="pug">
    router-link(:to="{name: 'estimate_planning_show', params: {id: row.id}}") {{ $root.estimate_number(row.estimate ? row.estimate : '') }}
</template>

<script>
export default {
    props: ['row'],
    created(){

    },
}
</script>