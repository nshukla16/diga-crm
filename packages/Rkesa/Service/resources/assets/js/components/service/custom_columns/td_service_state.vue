<template lang="pug">
    div(v-html="$root.get_visual_state(row)")
</template>

<script>
export default {
    props: ['row'],
}
</script>