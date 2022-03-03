<template lang="pug">
    span(v-if='row.service') {{ row.service.address }}
</template>

<script>
export default {
    props: ['row'],
}
</script>