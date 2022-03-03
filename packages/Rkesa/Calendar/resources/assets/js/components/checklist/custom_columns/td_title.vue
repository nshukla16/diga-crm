<template lang="pug">
    router-link(:to="{ name: 'checklist_show', params: {id: row.id }}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>