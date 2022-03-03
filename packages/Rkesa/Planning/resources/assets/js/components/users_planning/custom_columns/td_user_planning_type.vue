<template lang="pug">
    div
        router-link(v-if='row.is_custom === 1' :to="{name: 'user_planning_show', params: {id: row.id}}") {{ $t('gantt.Custom') }}
        router-link(v-if='row.is_custom === 0' :to="{name: 'user_planning_show', params: {id: row.id}}") {{ $t('gantt.Users') }}
        router-link(v-if='row.is_custom === 2' :to="{name: 'user_planning_show', params: {id: row.id}}") {{ $t('gantt.Groups') }}
</template>

<script>
export default {
    props: ['row'],
}
</script>