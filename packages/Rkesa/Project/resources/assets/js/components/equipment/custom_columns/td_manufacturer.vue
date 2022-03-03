<template lang="pug">
    div {{ row.manufacturer.name }}
</template>

<script>
export default {
    props: ['row'],
}
</script>