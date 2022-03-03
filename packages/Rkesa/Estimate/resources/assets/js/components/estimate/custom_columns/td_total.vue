<template lang="pug">
    div {{ $root.format_money(row.service.estimate_summ) }}
</template>

<script>
export default {
    props: ['row'],
}
</script>