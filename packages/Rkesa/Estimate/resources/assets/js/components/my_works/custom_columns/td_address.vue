<template lang="pug">
    span(v-if='row.estimate.service') {{ row.estimate.service.address }}
</template>

<script>
export default {
    props: ['row'],
}
</script>