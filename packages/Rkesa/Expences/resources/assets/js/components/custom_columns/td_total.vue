
<template lang="pug">
    div {{ row.total }} {{ $root.current_currency.symbol }}
</template>

<script>
export default {
    props: ['row'],
}
</script>