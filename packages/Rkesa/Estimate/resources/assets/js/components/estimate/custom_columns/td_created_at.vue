<template lang="pug">
    span {{ $root.format_datetime(value) }}
</template>

<script>
export default {
    props: ['value'],
}
</script>