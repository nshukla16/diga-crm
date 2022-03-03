<template lang="pug">
    div {{ $root.estimate_number(row.estimate) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>