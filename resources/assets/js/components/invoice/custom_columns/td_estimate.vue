<template lang="pug">
    div(v-if="row.estimate") {{ $root.estimate_number(row.estimate) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>