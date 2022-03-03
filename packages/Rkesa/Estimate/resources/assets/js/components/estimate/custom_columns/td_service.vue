<template lang="pug">
    span(v-html="$root.get_visual_state(row.service)")
</template>

<script>
export default {
    props: ['row'],
}
</script>