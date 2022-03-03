<template lang="pug">
    router-link(:to="{name: 'estimate_planning_show', params: {id: row.id}}") {{ row.is_custom ? $t('gantt.Custom') : $t('gantt.Estimate') }}
</template>

<script>
export default {
    props: ['row'],
}
</script>