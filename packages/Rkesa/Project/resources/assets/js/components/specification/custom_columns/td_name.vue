<template lang="pug">
    router-link(:to="{name: 'specification_edit', params: {id: row.id}}") {{ row.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>