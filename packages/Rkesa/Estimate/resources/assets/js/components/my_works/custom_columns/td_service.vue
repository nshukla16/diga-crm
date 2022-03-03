<template lang="pug">
    span(v-html="$root.get_visual_state(row.estimate.service)")
</template>

<script>
export default {
    props: ['row'],
}
</script>