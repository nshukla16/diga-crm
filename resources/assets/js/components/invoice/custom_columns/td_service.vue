<template lang="pug">
    div(v-if="row.service") {{ row.service.estimate_number }} {{ row.service.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>