<template lang="pug">
    span {{ row.estimate_unit.measure }}
</template>

<script>
export default {
    props: ['row'],
}
</script>