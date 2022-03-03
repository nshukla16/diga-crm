<template lang="pug">
    router-link(:to="{name: 'user_planning_show', params: {id: row.id}}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>