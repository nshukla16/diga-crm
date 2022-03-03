<template lang="pug">
    router-link(:to="{name: 'dashboard_edit', params: {id: row.id}}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>